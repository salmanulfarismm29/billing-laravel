<?php

namespace App\Traits;

trait CascadesSoftDeletes
{
    /**
     * Boot the CascadesSoftDeletes trait.
     */
    protected static function bootCascadesSoftDeletes()
    {
        static::deleting(function ($model) {
            if (!method_exists($model, 'cascadeDeletes') || empty($model->cascadeDeletes())) {
                return;
            }

            foreach ($model->cascadeDeletes() as $relationship) {
                if (method_exists($model, $relationship)) {
                    $relation = $model->{$relationship}();
                    
                    // Soft delete all related models
                    if ($model->isForceDeleting()) {
                        $relation->forceDelete();
                    } else {
                        $relation->delete();
                    }
                }
            }
        });
    }

    /**
     * Define the relationships to cascade delete.
     * Override this method in your models.
     */
    public function cascadeDeletes(): array
    {
        return [];
    }
}
