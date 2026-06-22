<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Liste les notifications de l'utilisateur connecté (avec le compte des non-lues)
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'unread_count' => Notification::where('user_id', $request->user()->id)->where('is_read', false)->count(),
            'data' => $notifications,
        ]);
    }

    // Marque une notification comme lue
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json($notification);
    }

    // Marque toutes les notifications de l'utilisateur comme lues
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }
}