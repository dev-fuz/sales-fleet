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

namespace Modules\Activities\Support;

use Illuminate\Support\Facades\Auth;
use Modules\Activities\Http\Resources\ActivityTypeResource;
use Modules\Activities\Models\ActivityType;

class ToScriptProvider
{
    /**
     * Provide the data to script.
     */
    public function __invoke(): array
    {
        if (! Auth::check()) {
            return [];
        }

        return [
            'activities' => [
                'defaults' => config('activities.defaults'),
                'default_activity_type_id' => ActivityType::getDefaultType(),

                'types' => ActivityTypeResource::collection(
                    ActivityType::withCommon()->orderBy('name')->get()
                ),
            ]];
    }
}
