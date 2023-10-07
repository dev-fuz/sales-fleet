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

namespace Modules\Deals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Concerns\HasDisplayOrder;
use Modules\Core\Models\CacheModel;
use Modules\Deals\Database\Factories\StageFactory;
use Modules\Users\Models\User;

class Stage extends CacheModel
{
    use HasDisplayOrder,
        HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'win_probability', 'display_order', 'pipeline_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'win_probability' => 'int',
        'display_order' => 'int',
        'pipeline_id' => 'int',
    ];

    /**
     * The columns for the model that are searchable.
     */
    protected static array $searchableColumns = [
        'name' => 'like',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($model->deals()->count() > 0) {
                abort(409, __('deals::deal.stage.delete_usage_warning'));
            }

            // We must delete the trashed deals when the stage is deleted
            // as we don't have any option to do with the deal except to associate
            // it with other stage (if found) but won't be accurate.
            $model->deals()->onlyTrashed()->get()->each->forceDelete();
        });
    }

    /**
     * A stage belongs to pipeline
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(\Modules\Deals\Models\Pipeline::class);
    }

    /**
     * A stage has many deals
     */
    public function deals(): HasMany
    {
        return $this->hasMany(\Modules\Deals\Models\Deal::class);
    }

    /**
     * Get all stages for that can be used on option fields.
     */
    public static function allStagesForOptions(User $user): Collection
    {
        return static::with('pipeline')
            ->whereHas('pipeline', fn ($query) => $query->visible($user))
            ->get()
            ->map(fn ($stage) => [
                'id' => $stage->getKey(),
                'name' => "{$stage->name} ({$stage->pipeline->name})",
            ]);
    }

    /**
     * Scope a query to only include only deals of the given pipeline.
     */
    public function scopeOfPipeline(Builder $query, Pipeline|int $pipeline): void
    {
        $query->where('pipeline_id', is_int($pipeline) ? $pipeline : $pipeline->getKey());
    }

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     */
    public function name(): Attribute
    {
        return Attribute::get(function (string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.stage.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Find stage by given ID.
     *
     * Caches results because of import to prevent thousands of queries.
     */
    public static function findFromObjectCache(int|string $id): Stage
    {
        return Cache::driver('array')->rememberForever(
            'deal-save-stage-'.$id, fn () => static::find($id)
        );
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): StageFactory
    {
        return StageFactory::new();
    }
}
