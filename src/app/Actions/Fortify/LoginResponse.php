<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Models\User;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->intended('/admin/attendances');
        }

        return redirect()->intended('/attendance');
    }
}
