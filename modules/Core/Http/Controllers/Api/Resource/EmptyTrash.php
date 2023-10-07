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
use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\TrashedResourceRequest;

class EmptyTrash extends ApiController
{
    public function __invoke(TrashedResourceRequest $request): JsonResponse
    {
        DB::transaction(function () use ($request) {
            $request->resource()
                ->trashedIndexQuery($request)
                ->lazyById(300)
                ->filter(fn ($model) => $request->user()->can('delete', $model))
                ->each(function ($model) use ($request) {
                    $request->resource()->forceDelete($model);
                });
        });

        return $this->response('', 204);
    }
}
