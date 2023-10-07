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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Facades\Fields;
use Modules\Core\Http\Controllers\ApiController;

class FieldSettingsController extends ApiController
{
    /**
     * Get the fields that are intended for the settings.
     */
    public function settings(string $group, string $view): JsonResponse
    {
        return $this->response(
            Fields::getForSettings($group, $view)
        );
    }

    /**
     * Get fields for settings in bulk in given groups.
     */
    public function bulkSettings(string $view, Request $request): JsonResponse
    {
        return $this->response(
            $request->collect('groups')->mapWithKeys(
                fn ($group) => [$group => Fields::getForSettings($group, $view)]
            )
        );
    }

    /**
     * Save the customized fields from settings.
     */
    public function update(string $group, string $view, Request $request): JsonResponse
    {
        Fields::customize($request->post(), $group, $view);

        return $this->response('', 204);
    }

    /**
     * Reset the customized fields for a view.
     */
    public function destroy(string $group, string $view): JsonResponse
    {
        Fields::customize([], $group, $view);

        return $this->response(
            Fields::getForSettings($group, $view)
        );
    }
}
