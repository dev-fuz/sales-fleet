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

namespace Modules\Users\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Actions\DestroyableAction;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\User;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Users\Models\User as UserModel;

class UserDelete extends DestroyableAction
{
    /**
     * Handle method
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        // User delete action flag

        $resource = Innoclapps::resourceByModel(UserModel::class);

        // Ensure that if the current user is provided as delete ID is always as first user
        // so it can fail early in the service "delete" method.
        $currentUser = $models->first(fn (UserModel $user) => $user->is(Auth::user()));

        if ($currentUser) {
            $models = $models->reject(fn (UserModel $user) => $user->is(Auth::user()));
            $models->prepend($currentUser);
        }

        DB::transaction(function () use ($models, $fields, $resource) {
            foreach ($models as $model) {
                $resource->delete($model, (int) $fields->user_id);
            }
        });
    }

    /**
     * Query the models for execution.
     */
    protected function findModelsForExecution(array $ids, Builder $query): EloquentCollection
    {
        return $query->with(
            ['personalEmailAccounts', 'oAuthAccounts', 'connectedCalendars', 'comments', 'imports']
        )->findMany($ids);
    }

    /**
     * Get the action fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            User::make('')
                ->help(__('users::user.transfer_data_info'))
                ->helpDisplay('text')
                ->rules('required'),
        ];
    }

    /**
     * @param  \Illumindate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->isSuperAdmin();
    }

    /**
     * Action name
     */
    public function name(): string
    {
        return __('users::user.actions.delete');
    }
}
