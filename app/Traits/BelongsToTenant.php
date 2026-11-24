<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait for a model.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->workspace_id)) {
                $workspaceId = null;

                if (app()->bound('current_workspace')) {
                    $workspace = app('current_workspace');
                    $workspaceId = $workspace?->id;
                }

                if (!$workspaceId && session()->has('active_workspace_id')) {
                    $workspaceId = session('active_workspace_id');
                }

                if ($workspaceId) {
                    $model->workspace_id = $workspaceId;
                }
            }
        });
    }

    /**
     * Get the workspace that owns the model.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
