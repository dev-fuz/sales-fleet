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

namespace Modules\Core\Support\Timeline;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Core\Models\PinnedTimelineSubject;
use Modules\Core\Support\Changelog\LogsModelChanges;
use Modules\Core\Support\Changelog\LogsModelPivotChanges;

trait HasTimeline
{
    use LogsModelChanges,
        LogsModelPivotChanges;

    /**
     * Boot the HasTimeline trait
     */
    protected static function bootHasTimeline(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->loadMissing('pinnedTimelineables')->pinnedTimelineables->each->delete();
            }
        });
    }

    /**
     * Get the timeline subject key
     */
    public static function getTimelineSubjectKey(): string
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the subject pinned timelineables models
     */
    public function pinnedTimelineables(): MorphMany
    {
        return $this->morphMany(PinnedTimelineSubject::class, 'subject');
    }

    /**
     * Get the lang attribute for the changelog when logging to the pivot model
     * that the related model is moved to the trash.
     */
    protected static function modelTrashedPivotChangelogLangAttribute($model): array
    {
        return [
            'key' => 'core::timeline.associate_trashed',
            'attrs' => ['displayName' => $model->display_name],
        ];
    }

    /**
     * Get the lang attribute for the changelog when logging to the pivot model
     * that the related model is restored.
     */
    protected static function modelRestoredPivotChangelogLangAttribute($model): array
    {
        return [
            'key' => 'core::timeline.association_restored',
            'attrs' => ['associationDisplayName' => $model->display_name],
        ];
    }

    /**
     * Get the lang attribute for the changelog when logging to the pivot model
     * that the related model is permanently deleted.
     */
    protected static function modelPermanentlyDeletedPivotChangelogLangAttribute($model): array
    {
        return [
            'key' => 'core::timeline.association_permanently_deleted',
            'attrs' => ['associationDisplayName' => $model->display_name],
        ];
    }
}
