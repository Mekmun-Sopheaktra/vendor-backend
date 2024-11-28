<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyRegisterEmail extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $username = $notifiable->name;
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->view('auth.verify-email', [
                'verificationUrl' => $verificationUrl,
                'username' => $username,
                'appName' => $appName
            ]);
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->email)]
        );
    }
}

//{
//    "name" : "La SAMYOUNG",
//    "email" : "laseavyongg@gmail.com",
//    "password" : "Aa123123$"
//}
