<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SuscripcionesCobros;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuscripcionesCobrosPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SuscripcionesCobros');
    }

    public function view(AuthUser $authUser, SuscripcionesCobros $suscripcionesCobros): bool
    {
        return $authUser->can('View:SuscripcionesCobros');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SuscripcionesCobros');
    }

    public function update(AuthUser $authUser, SuscripcionesCobros $suscripcionesCobros): bool
    {
        return $authUser->can('Update:SuscripcionesCobros');
    }

    public function delete(AuthUser $authUser, SuscripcionesCobros $suscripcionesCobros): bool
    {
        return $authUser->can('Delete:SuscripcionesCobros');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SuscripcionesCobros');
    }

    public function restore(AuthUser $authUser, SuscripcionesCobros $suscripcionesCobros): bool
    {
        return $authUser->can('Restore:SuscripcionesCobros');
    }

    public function forceDelete(AuthUser $authUser, SuscripcionesCobros $suscripcionesCobros): bool
    {
        return $authUser->can('ForceDelete:SuscripcionesCobros');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SuscripcionesCobros');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SuscripcionesCobros');
    }

    public function replicate(AuthUser $authUser, SuscripcionesCobros $suscripcionesCobros): bool
    {
        return $authUser->can('Replicate:SuscripcionesCobros');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SuscripcionesCobros');
    }

}