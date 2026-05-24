<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuscripcionesTarifasPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $user): bool
    {
        return $user->can('ViewAny:SuscripcionesTarifas');
    }

    public function view(AuthUser $user): bool
    {
        return $user->can('View:SuscripcionesTarifas');
    }

    public function create(AuthUser $user): bool
    {
        return $user->can('Create:SuscripcionesTarifas');
    }

    public function update(AuthUser $user, $model = null): bool
    {
        return $user->can('Update:SuscripcionesTarifas');
    }

    public function delete(AuthUser $user, $model = null): bool
    {
        return $user->can('Delete:SuscripcionesTarifas');
    }
}
