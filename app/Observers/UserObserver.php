<?php

namespace App\Observers;

use App\Mail\BienvenidaSetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function created(User $user): void
    {
        // No enviar durante seeders ni comandos de consola
        if (app()->runningInConsole()) {
            return;
        }

        Mail::to($user->email)->send(new BienvenidaSetPasswordMail($user));
    }
}
