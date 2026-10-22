<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class ComplaintAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['pictures','user'])
            ->orderByDesc('created_at');

        if ($request->status)    $query->where('status', $request->status);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)   $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('complaint_number','like',"%$s%")
                  ->orWhere('full_name','like',"%$s%")
                  ->orWhere('cnic','like',"%$s%")
                  ->orWhere('item_name','like',"%$s%")
                  ->orWhere('shop_name','like',"%$s%")
            );
        }

        $pendingCount = Complaint::where('status','pending')->count();
        $totalCount   = $query->count();
        $complaints   = $query->paginate(20);

        return view('complaints.index', compact('complaints','pendingCount','totalCount'));
    }

    public function show($id)
    {
        $complaint    = Complaint::with(['pictures','user','statusLogs'])->findOrFail($id);
        $pendingCount = Complaint::where('status','pending')->count();
        return view('complaints.show', compact('complaint','pendingCount'));
    }

    public function updateStatus(Request $request, $id)
    {
        $this->denyIfViewer();
        $request->validate([
            'status'        => 'required|in:pending,in_progress,resolved,rejected',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $complaint->update([
            'status'        => $request->status,
            'admin_remarks' => $request->admin_remarks,
        ]);

        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'old_status'   => $oldStatus,
            'new_status'   => $request->status,
            'changed_by'   => auth()->id(),
            'remarks'      => $request->admin_remarks,
        ]);

        UserNotification::create([
            'user_id' => $complaint->user_id,
            'type'    => 'complaint_update',
            'title'   => "Complaint {$complaint->complaint_number} Updated",
            'body'    => "Your complaint status is now: " . ucfirst(str_replace('_',' ',$request->status)),
            'data'    => json_encode(['complaint_id' => $complaint->id]),
        ]);

        return redirect()->route('admin.complaints.show', $id)
            ->with('success', 'Status updated successfully.');
    }

    public function export(Request $request)
    {
        $query = Complaint::orderByDesc('created_at');
        if ($request->status)    $query->where('status', $request->status);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)   $query->whereDate('created_at', '<=', $request->date_to);

        $complaints = $query->get();
        $csv = "Complaint#,Name,CNIC,Mobile,Item,Shop,Location,Status,Date\n";
        foreach ($complaints as $c) {
            $csv .= implode(',', [
                $c->complaint_number, "\"{$c->full_name}\"", $c->cnic, $c->mobile,
                "\"{$c->item_name}\"", "\"{$c->shop_name}\"",
                "\"{$c->location_address}\"", $c->status,
                $c->created_at->format('d M Y'),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="complaints_' . date('Ymd') . '.csv"');
    }
}
