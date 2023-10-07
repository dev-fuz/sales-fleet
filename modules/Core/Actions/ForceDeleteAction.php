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

namespace Modules\Core\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Requests\ActionRequest;

class ForceDeleteAction extends Action
{
    /**
     * Handle method.
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        $resource = Innoclapps::resourceByModel($models[0]);

        DB::transaction(function () use ($models, $resource) {
            foreach ($models as $model) {
                if ($resource) {
                    $resource->forceDelete($model);
                } else {
                    $model->forceDelete();
                }
            }
        });
    }

    /**
     * @param  \Illumindate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->can('delete', $model);
    }

    /**
     * Query the models for execution.
     */
    protected function findModelsForExecution(array $ids, Builder $query): EloquentCollection
    {
        return $query->withTrashed()->findMany($ids);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('core::app.soft_deletes.force_delete');
    }

    /**
     * toArray.
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), ['destroyable' => true]);
    }
}
