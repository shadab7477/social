<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $workspaceId = null;

        if (app()->bound('current_workspace')) {
            $workspace = app('current_workspace');
            $workspaceId = $workspace?->id;
        }

        if (!$workspaceId && session()->has('active_workspace_id')) {
            $workspaceId = session('active_workspace_id');
        }

        if ($workspaceId) {
            $builder->where($model->getTable() . '.workspace_id', $workspaceId);
        }
    }
}
