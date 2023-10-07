<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/** @mixin \Modules\Core\Models\Model */
trait HasCreator
{
    /**
     * Boot HasCreator trait.
     */
    protected static function bootHasCreator(): void
    {
        static::creating(function ($model) {
            $foreignKey = $model->getCreatorForeignKeyName();

            if (is_null($model->{$foreignKey}) && Auth::check()) {
                $model->forceFill([
                    $foreignKey => Auth::id(),
                ]);
            }
        });
    }

    /**
     * A model has creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            \Modules\Users\Models\User::class,
            $this->getCreatorForeignKeyName()
        );
    }

    /**
     * Get the creator foreign key name.
     */
    public function getCreatorForeignKeyName(): string
    {
        return 'created_by';
    }
}
