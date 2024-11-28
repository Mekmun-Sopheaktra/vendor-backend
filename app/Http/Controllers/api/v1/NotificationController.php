<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationCollection;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use BaseApiResponse;

    public function index()
    {
        return $this->success(new NotificationCollection(auth()->user()->notifications), 'Notifications', 'Notifications fetched successfully');
    }

    //read notification
    public function read(Request $request)
    {
        $notification = auth()->user()->notifications()->find($request->id);

        if (!$notification) {
            return $this->error('Notification not found', 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return $this->success($notification, 'Notification', 'Notification marked as read successfully');
    }

    public function unread()
    {
        $unread = auth()->user()->unreadNotifications;
        $unread->each(function ($notification) {
            $notification->markAsRead();
        });

        return $this->success(new NotificationCollection($unread), 'Notifications', 'Unread notifications fetched successfully');
    }
}
