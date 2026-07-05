<?php

namespace App\Listeners;

use App\Events\UserLoggedOut;
use App\Mail\LogoutAlertMail;
use Illuminate\Support\Facades\Mail;


class SendLogoutNotification
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }


    /**
     * Handle the event.
     */
    public function handle(UserLoggedOut $event): void
    {
        Mail::to($event->user->email)
                        ->queue((new LogoutAlertMail($event->user))
                        ->onQueue('auth_mails'));




    }
}
