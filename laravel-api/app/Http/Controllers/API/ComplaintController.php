<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintPicture;
use App\Models\ComplaintStatusLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    // ─────────────────────────────────────────────
    //  LIST (user's own)
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Complaint::with('pictures')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $complaints = $query->paginate(10);

        $complaints->getCollection()->transform(function ($c) {
            return $this->formatComplaint($c);
        });

        return response()->json([
            'success' => true,
            'data'    => $complaints,
        ]);
    }

    // ─────────────────────────────────────────────
    //  STORE (file complaint)
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'        => 'required|string|max:100',
            'cnic'             => 'required|string',
            'mobile'           => 'required|string|max:15',
            'item_name'        => 'required|string|max:100',
            'shop_name'        => 'required|string|max:150',
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'location_address' => 'required|string|max:255',
            'details'          => 'required|string|max:500',
            'pictures'         => 'required|array|min:1|max:5',
            'pictures.*'       => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Generate complaint number
        $complaintNumber = 'CMPL-' . date('Y') . '-' . str_pad(
            Complaint::whereYear('created_at', date('Y'))->count() + 1,
            5, '0', STR_PAD_LEFT
        );

        $complaint = Complaint::create([
            'user_id'          => $request->user()->id,
            'complaint_number' => $complaintNumber,
            'full_name'        => $request->full_name,
            'cnic'             => $request->cnic,
            'mobile'           => $request->mobile,
            'item_name'        => $request->item_name,
            'shop_name'        => $request->shop_name,
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'location_address' => $request->location_address,
            'details'          => $request->details,
            'status'           => 'pending',
        ]);

        // Store pictures
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $pic) {
                $path = $pic->store("complaints/{$complaint->id}", 'public');
                ComplaintPicture::create([
                    'complaint_id' => $complaint->id,
                    'path'         => $path,
                    'url'          => Storage::url($path),
                ]);
            }
        }

        // Notify admin (send notification)
        $this->notifyAdmin($complaint);

        return response()->json([
            'success' => true,
            'message' => 'Complaint filed successfully',
            'data'    => $this->formatComplaint($complaint->load('pictures')),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  SHOW (single complaint)
    // ─────────────────────────────────────────────
    public function show(Request $request, $id): JsonResponse
    {
        $complaint = Complaint::with(['pictures', 'statusLogs'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatComplaint($complaint),
        ]);
    }

    // ─────────────────────────────────────────────
    //  TRACK by complaint number (public, no auth)
    // ─────────────────────────────────────────────
    public function track($complaintNumber): JsonResponse
    {
        $complaint = Complaint::with(['pictures', 'statusLogs'])
            ->where('complaint_number', $complaintNumber)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $this->formatComplaint($complaint),
        ]);
    }

    // ─────────────────────────────────────────────
    //  STATS (dashboard)
    // ─────────────────────────────────────────────
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'success' => true,
            'data'    => [
                'total'       => Complaint::where('user_id', $userId)->count(),
                'pending'     => Complaint::where('user_id', $userId)->where('status', 'pending')->count(),
                'in_progress' => Complaint::where('user_id', $userId)->where('status', 'in_progress')->count(),
                'resolved'    => Complaint::where('user_id', $userId)->where('status', 'resolved')->count(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: List all complaints
    // ─────────────────────────────────────────────
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Complaint::with(['pictures', 'user', 'statusLogs'])
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('complaint_number', 'like', "%{$request->search}%")
                  ->orWhere('full_name', 'like', "%{$request->search}%")
                  ->orWhere('item_name', 'like', "%{$request->search}%")
                  ->orWhere('shop_name', 'like', "%{$request->search}%");
            });
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $complaints = $query->paginate(20);
        $complaints->getCollection()->transform(fn($c) => $this->formatComplaint($c));

        return response()->json(['success' => true, 'data' => $complaints]);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: Update status
    // ─────────────────────────────────────────────
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $this->denyIfViewer();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $complaint->update([
            'status'        => $request->status,
            'admin_remarks' => $request->admin_remarks,
        ]);

        // Log status change
        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'old_status'   => $oldStatus,
            'new_status'   => $request->status,
            'changed_by'   => $request->user()?->id,
            'remarks'      => $request->admin_remarks,
        ]);

        // Send notification to user
        $this->notifyUser($complaint, $oldStatus, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'data'    => $this->formatComplaint($complaint->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: Stats
    // ─────────────────────────────────────────────
    public function adminStats(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total'         => Complaint::count(),
                'pending'       => Complaint::where('status', 'pending')->count(),
                'in_progress'   => Complaint::where('status', 'in_progress')->count(),
                'resolved'      => Complaint::where('status', 'resolved')->count(),
                'rejected'      => Complaint::where('status', 'rejected')->count(),
                'today'         => Complaint::whereDate('created_at', today())->count(),
                'this_month'    => Complaint::whereMonth('created_at', now()->month)->count(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  Helper: Format complaint response
    // ─────────────────────────────────────────────
    private function formatComplaint(Complaint $c): array
    {
        return [
            'id'               => $c->id,
            'complaint_number' => $c->complaint_number,
            'full_name'        => $c->full_name,
            'cnic'             => $c->cnic,
            'mobile'           => $c->mobile,
            'item_name'        => $c->item_name,
            'shop_name'        => $c->shop_name,
            'latitude'         => $c->latitude,
            'longitude'        => $c->longitude,
            'location_address' => $c->location_address,
            'details'          => $c->details,
            'status'           => $c->status,
            'admin_remarks'    => $c->admin_remarks,
            'pictures'         => $c->pictures ? $c->pictures->map(fn($p) => request()->getSchemeAndHttpHost() . '/storage/' . $p->path)->values() : [],
            'status_history'   => $c->statusLogs->map(fn($l) => [
                'old_status' => $l->old_status,
                'new_status' => $l->new_status,
                'remarks'    => $l->remarks,
                'created_at' => $l->created_at?->format('d M Y - h:i A'),
            ]),
            'created_date'     => $c->created_at?->format('d M Y'),
            'submitted_at'     => $c->created_at?->format('d M Y - h:i A'),
        ];
    }

    private function notifyAdmin(Complaint $complaint): void
    {
        // TODO: send FCM/email to admin
        // Use Laravel Notifications or Firebase
    }

    private function notifyUser(Complaint $complaint, string $old, string $new): void
    {
        // Create in-app notification for user
        \App\Models\UserNotification::create([
            'user_id' => $complaint->user_id,
            'type'    => 'complaint_update',
            'title'   => "Complaint #{$complaint->complaint_number} Status Updated",
            'body'    => "Your complaint status changed from {$old} to {$new}.",
            'data'    => json_encode(['complaint_id' => $complaint->id]),
        ]);
    }
}
