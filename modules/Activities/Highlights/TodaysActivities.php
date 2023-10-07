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

namespace Modules\Activities\Highlights;

use Modules\Activities\Criteria\ViewAuthorizedActivitiesCriteria;
use Modules\Activities\Models\Activity;
use Modules\Core\Menu\Highlights\Highlight;
use Modules\Core\Models\Filter;

class TodaysActivities extends Highlight
{
    /**
     * Get the highlight name
     */
    public function name(): string
    {
        return __('activities::activity.highlights.todays');
    }

    /**
     * Get the highligh count
     */
    public function count(): int
    {
        return Activity::dueToday()->criteria(ViewAuthorizedActivitiesCriteria::class)->count();
    }

    /**
     * Get the background color variant when the highlight count is bigger then zero
     */
    public function backgroundColorVariant(): string
    {
        return 'warning';
    }

    /**
     * Get the front-end route that the highly will redirect to
     */
    public function route(): array|string
    {
        $filter = Filter::findByFlag('due-today-activities');

        return [
            'name' => 'activity-index',
            'query' => [
                'filter_id' => $filter?->id,
            ],
        ];
    }
}
