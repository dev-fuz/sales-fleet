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

namespace Modules\Deals\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Deals\Models\Stage;

class SummaryService
{
    /**
     * Get the deals summary (by stage)
     */
    public function calculate(Builder $query, int $pipelineId = null, int $stageId = null): Collection
    {
        return Stage::select(['id', 'win_probability'])
            ->when(is_int($pipelineId), fn ($query) => $query->ofPipeline($pipelineId))
            ->when(is_int($stageId), fn ($query) => $query->where('id', $stageId))
            ->get()
            ->mapWithKeys(function (Stage $stage) use ($query) {
                return [$stage->id => [
                    'count' => (int) $query->clone()->where('stage_id', $stage->id)->count(),
                    'value' => (float) $sum = $query->clone()->where('stage_id', $stage->id)->sum('amount'),
                    // Not applicable when the user is filtering won or lost deals
                    'weighted_value' => $stage->win_probability * $sum / 100,
                ]];
            });
    }
}
