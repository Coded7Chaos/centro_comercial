<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Suscripciones;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuscripcionesPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Suscripciones');
    }

    public function view(AuthUser $authUser, Suscripciones $suscripciones): bool
    {
        return $authUser->can('View:Suscripciones');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Suscripciones');
    }

    public function update(AuthUser $authUser, Suscripciones $suscripciones): bool
    {
        return $authUser->can('Update:Suscripciones');
    }

    public function delete(AuthUser $authUser, Suscripciones $suscripciones): bool
    {
        return $authUser->can('Delete:Suscripciones');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Suscripciones');
    }

    public function restore(AuthUser $authUser, Suscripciones $suscripciones): bool
    {
        return $authUser->can('Restore:Suscripciones');
    }

    public function forceDelete(AuthUser $authUser, Suscripciones $suscripciones): bool
    {
        return $authUser->can('ForceDelete:Suscripciones');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Suscripciones');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Suscripciones');
    }

    public function replicate(AuthUser $authUser, Suscripciones $suscripciones): bool
    {
        return $authUser->can('Replicate:Suscripciones');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Suscripciones');
    }

}