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

namespace Modules\Deals\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Http\Resources\DealResource;
use Modules\Deals\Models\Deal;

class DealStatusController extends ApiController
{
    /**
     * Change the deal status.
     *
     * @deprecated
     */
    public function handle(Deal $deal, $status, Request $request): JsonResponse
    {
        $this->authorize('update', $deal);

        $status = DealStatus::find($status);

        // User must unmark the deal as open when the deal status is won or lost in order to change any further statuses
        abort_if(
            $deal->isStatusLocked($status),
            409,
            'The deal first must be marked as open in order to apply the '.$status->name.' status.'
        );

        $request->validate([
            'lost_reason' => [
                Rule::requiredIf(settings('lost_reason_is_required')),
                'nullable', 'string', 'max:191',
            ],
        ]);

        $deal->fillStatus($status, $request->lost_reason)->save();

        return $this->response(
            new DealResource(
                $deal->resource()->displayQuery()->find($deal->id)
            )
        );
    }
}
