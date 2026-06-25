<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the Auditable trait.
     */
    protected static function bootAuditable()
    {
        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::id();
            }
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
        
        static::deleting(function ($model) {
            // Check if model uses SoftDeletes
            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->isForceDeleting() && !$model->isDirty('deleted_by')) {
                    $model->deleted_by = Auth::id();
                    // Save silently so we don't trigger another updating event
                    $model->saveQuietly();
                }
            }
        });
    }
}
