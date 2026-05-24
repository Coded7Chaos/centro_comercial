<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SuscripcionesPagos;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuscripcionesPagosPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SuscripcionesPagos');
    }

    public function view(AuthUser $authUser, SuscripcionesPagos $suscripcionesPagos): bool
    {
        return $authUser->can('View:SuscripcionesPagos');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SuscripcionesPagos');
    }

    public function update(AuthUser $authUser, SuscripcionesPagos $suscripcionesPagos): bool
    {
        return $authUser->can('Update:SuscripcionesPagos');
    }

    public function delete(AuthUser $authUser, SuscripcionesPagos $suscripcionesPagos): bool
    {
        return $authUser->can('Delete:SuscripcionesPagos');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SuscripcionesPagos');
    }

    public function restore(AuthUser $authUser, SuscripcionesPagos $suscripcionesPagos): bool
    {
        return $authUser->can('Restore:SuscripcionesPagos');
    }

    public function forceDelete(AuthUser $authUser, SuscripcionesPagos $suscripcionesPagos): bool
    {
        return $authUser->can('ForceDelete:SuscripcionesPagos');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SuscripcionesPagos');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SuscripcionesPagos');
    }

    public function replicate(AuthUser $authUser, SuscripcionesPagos $suscripcionesPagos): bool
    {
        return $authUser->can('Replicate:SuscripcionesPagos');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SuscripcionesPagos');
    }

}