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

namespace Modules\Activities\Fields;

use Modules\Activities\Http\Resources\GuestResource;
use Modules\Activities\Services\ActivityGuestService;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;

class GuestsSelect extends Field
{
    /**
     * Field component.
     */
    public static $component = 'guests-select-field';

    /**
     * Initialize new GuestsSelect instance.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->toggleable()
            ->provideSampleValueUsing(fn () => [])
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value, string $requestAttribute) {
                if (! is_array($value)) {
                    return;
                }

                return function () use ($model, $value, $request) {
                    (new ActivityGuestService())->save($model, $this->parseGuestsForSave($value, $request));
                };
            });
    }

    /**
     * Resolve the displayable field value (for mail placeholders)
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        $value = parent::resolveForDisplay($model);

        if ($value->isNotEmpty()) {
            $value->loadMissing('guestable');

            return $value->map(fn ($guest) => $guest->guestable)->map->getGuestDisplayName()->implode(', ');
        }

        return null;
    }

    /**
     * Resolve the field value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if ($model->relationLoaded('guests')) {
            return ['guests' => GuestResource::collection($model->guests)];
        }
    }

    /**
     * Parse the given guests array for save
     */
    protected function parseGuestsForSave(array $guests, ResourceRequest $request): array
    {
        $parsed = [];

        foreach ($guests as $resourceName => $ids) {
            $parsed = array_merge(
                $parsed,
                $request->findResource($resourceName)->newQuery()->findMany($ids)->all()
            );
        }

        return $parsed;
    }
}
