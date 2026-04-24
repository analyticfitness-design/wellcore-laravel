<?php

namespace App\Scopes;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * RLS aplicativo: limita las queries al client_id del usuario autenticado
 * SOLO cuando el usuario autenticado es un Client. Admins y coaches no
 * son afectados (el scope se auto-desactiva) porque sus controllers
 * ya filtran por listas de client_id via getCoachClientIds().
 *
 * Rutas CLI/queue/webhook (sin auth) tampoco son afectadas: el scope se
 * vuelve no-op cuando auth()->user() es null.
 */
class OwnedByClientScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('wellcore')->user();

        if ($user instanceof Client) {
            $builder->where($model->qualifyColumn('client_id'), $user->id);
        }
    }
}
