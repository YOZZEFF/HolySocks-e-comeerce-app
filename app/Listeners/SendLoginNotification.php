<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Mail\LoginAlertMail;
use Illuminate\Support\Facades\Mail;
class SendLoginNotification
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(UserLoggedIn $event): void
    {
        Mail::to($event->user->email)
                        ->queue((new LoginAlertMail($event->user))
                        ->onQueue('auth_mails'));

    }
}
