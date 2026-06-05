<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class FirstLoginController extends Controller
{
    /**
     * Muestra el formulario para crear la contraseña.
     * La ruta está protegida por el middleware 'signed', que verifica
     * que la URL no fue manipulada y que no expiró (7 días).
     */
    public function show(Request $request, User $user): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'El enlace de activación ha expirado o no es válido. Contacta al administrador.']);
        }

        return view('auth.first-login', compact('user'));
    }

    /**
     * Guarda la contraseña elegida por el usuario y lo autentifica.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'El enlace de activación ha expirado. Contacta al administrador.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user->update([
            'password'          => Hash::make($request->password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        Auth::login($user);

        // Redirigir según el rol del usuario
        if ($user->hasRole(['super_admin', 'admin', 'panel_user'])) {
            return redirect('/admin');
        }

        return redirect()->route('cliente.dashboard');
    }
}
