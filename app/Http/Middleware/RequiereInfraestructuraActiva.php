<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\ActiveInfraestructura;
use App\Models\Infraestructuras;

class RequiereInfraestructuraActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (
            $user &&
            ($user->hasRole('admin') || $user->hasRole('super_admin')) &&
            !ActiveInfraestructura::isSet() &&
            Infraestructuras::exists()
        ) {
            // Avoid redirect loop: don't redirect if already on the selector page
            // or on any Livewire/Filament internal endpoint
            $path = ltrim($request->path(), '/');

            $bypass = [
                'admin/seleccionar-infraestructura',
                'livewire/update',
                'livewire/upload-file',
                'filament',
            ];

            foreach ($bypass as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    return $next($request);
                }
            }

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return $next($request);
            }

            return redirect()->route('filament.admin.pages.seleccionar-infraestructura');
        }

        return $next($request);
    }
}
