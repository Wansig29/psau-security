<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a user's notification as read.
     */
    public function markAsRead(Request $request, $notification): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $notification->markAsRead();

        return back();
    }
}

