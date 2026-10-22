<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    // ─────────────────────────────────────────────
    //  LIST user notifications
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $notifications->getCollection()->transform(fn($n) => $this->format($n));

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    // ─────────────────────────────────────────────
    //  UNREAD COUNT
    // ─────────────────────────────────────────────
    public function unreadCount(Request $request): JsonResponse
    {
        $count = UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['success' => true, 'count' => $count]);
    }

    // ─────────────────────────────────────────────
    //  MARK SINGLE AS READ
    // ─────────────────────────────────────────────
    public function markRead(Request $request, $id): JsonResponse
    {
        $notif = UserNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notif->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Marked as read']);
    }

    // ─────────────────────────────────────────────
    //  MARK ALL AS READ
    // ─────────────────────────────────────────────
    public function markAllRead(Request $request): JsonResponse
    {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'All marked as read']);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: Broadcast notification to all users
    // ─────────────────────────────────────────────
    public function broadcast(Request $request): JsonResponse
    {
        $this->denyIfViewer();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'body'  => 'required|string|max:500',
            'type'  => 'required|in:announcement,price_alert,system',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userIds = \App\Models\User::where('role', 'citizen')->pluck('id');

        $inserts = $userIds->map(fn($uid) => [
            'user_id'    => $uid,
            'type'       => $request->type,
            'title'      => $request->title,
            'body'       => $request->body,
            'data'       => null,
            'read_at'    => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        // Chunk insert for performance
        foreach (array_chunk($inserts, 500) as $chunk) {
            UserNotification::insert($chunk);
        }

        return response()->json([
            'success' => true,
            'message' => "Notification sent to {$userIds->count()} users",
        ]);
    }

    private function format(UserNotification $n): array
    {
        return [
            'id'          => $n->id,
            'type'        => $n->type,
            'title'       => $n->title,
            'body'        => $n->body,
            'data'        => $n->data ? json_decode($n->data, true) : null,
            'read_at'     => $n->read_at,
            'created_ago' => $n->created_at?->diffForHumans(),
            'created_at'  => $n->created_at?->format('d M Y h:i A'),
        ];
    }
}
