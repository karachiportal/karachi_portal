<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationAdminController extends Controller
{
    public function index()
    {
        $totalCitizens    = User::where('role','citizen')->count();
        $recentBroadcasts = UserNotification::selectRaw('title, body, type, created_at, COUNT(*) as recipient_count')
            ->groupBy('title','body','type','created_at')
            ->orderByDesc('created_at')
            ->limit(10)->get();
        $pendingCount = Complaint::where('status','pending')->count();
        return view('notifications.index', compact('totalCitizens','recentBroadcasts','pendingCount'));
    }

    public function broadcast(Request $request)
    {
        $this->denyIfViewer();
        $request->validate(['title'=>'required|string','body'=>'required|string','type'=>'required']);
        $userIds = User::where('role','citizen')->pluck('id');
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
        foreach (array_chunk($inserts, 500) as $chunk) {
            UserNotification::insert($chunk);
        }
        return redirect()->route('admin.notifications.index')
            ->with('success', "Notification sent to {$userIds->count()} citizens.");
    }
}
