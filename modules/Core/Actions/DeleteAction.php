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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Requests\ActionRequest;

class DeleteAction extends DestroyableAction
{
    /**
     * Indicates that the action will be hidden on the index view.
     */
    public bool $hideOnIndex = true;

    /**
     * Indicates whether the action bulk deletes models.
     */
    public bool $isBulk = false;

    /**
     * Authorized to run callback
     *
     * @var callable
     */
    protected $authorizedToRunWhen;

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
                    $resource->delete($model);
                } else {
                    $model->delete();
                }
            }
        });
    }

    /**
     * Add authorization callback for the action.
     */
    public function authorizedToRunWhen(callable $callable): static
    {
        $this->authorizedToRunWhen = $callable;

        return $this;
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        if (! $this->authorizedToRunWhen) {
            return $request->user()->can('delete', $model);
        }

        return call_user_func_array($this->authorizedToRunWhen, [$request, $model]);
    }

    /**
     * Set that the action is bulk action.
     */
    public function isBulk(): static
    {
        $this->onlyOnIndex();
        $this->isBulk = true;

        return $this;
    }

    /**
     * Get the URI key for the card.
     */
    public function uriKey(): string
    {
        $key = 'delete';

        return ($this->isBulk ? 'bulk-' : '').$key;
    }
}
