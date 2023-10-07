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

namespace Modules\Deals\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Fields\Select;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Deals\Models\Stage;

class ChangeDealStage extends Action
{
    /**
     * Indicates that the action will be hidden on the update view.
     */
    public bool $hideOnUpdate = true;

    /**
     * Handle method.
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        foreach ($models as $model) {
            $model->forceFill([
                'stage_id' => $fields->stage_id,
            ])->save();
        }
    }

    /**
     * Get the action fields.
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Select::make('stage_id', __('deals::fields.deals.stage.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->rules('required')
                ->options(function () use ($request) {
                    return Stage::allStagesForOptions($request->user());
                }),
        ];
    }

    /**
     * @param  \Illumindate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->can('update', $model);
    }

    /**
     * Query the models for execution.
     */
    protected function findModelsForExecution(array $ids, Builder $query): EloquentCollection
    {
        return $query->with('stage')->findMany($ids);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('deals::deal.actions.change_stage');
    }
}
