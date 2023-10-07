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

namespace Modules\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use ReflectionClass;
use ReflectionMethod;

trait ProvidesModelAuthorizations
{
    /**
     * Get all defined authorizations for the model.
     */
    public function getAuthorizations(Model $model, array $exclude = []): ?array
    {
        if ($policy = policy($model)) {
            return collect((new ReflectionClass($policy))->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(function ($method) use ($exclude) {
                    return in_array($method->name, array_merge($exclude, ['denyAsNotFound', 'denyWithStatus', 'before']));
                })
                ->mapWithKeys(fn ($method) => [$method->name => Gate::allows($method->name, $model)])->all();
        }

        return null;
    }
}
