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
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\CreateResourceRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Requests\UpdateResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\AssociatesResources;

class ResourceController extends ApiController
{
    use AssociatesResources;

    /**
     * Display a listing of the resource.
     */
    public function index(ResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof HasOperations, 404);

        // Resourceful index flag
        $this->authorize('viewAny', $request->resource()::$model);

        $results = $request->resource()
            ->indexQuery($request)
            ->paginate($request->integer('per_page') ?: null);

        return $this->response(
            $request->toResponse($results)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof HasOperations, 404);

        // Resourceful store flag
        $this->authorize('create', $request->resource()::$model);

        $request->performValidation();

        $record = DB::transaction(function () use ($request) {
            $record = $request->resource()->create(
                $request->newHydratedModel(),
                $request
            );

            $this->handleCustomAssociatedResources($record, $request);

            return $record;
        });

        return $this->response(
            $request->toResponse(
                tap($request->resource()->displayQuery()->find($record->getKey()), function ($record) {
                    // Set that this record was recently created as the property value is lost
                    // because we are re-querying the record again after creation
                    $record->wasRecentlyCreated = true;
                })
            ),
            201
        );
    }

    /**
     * Display resource record.
     */
    public function show(ResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof HasOperations, 404);

        // Resourceful show flag
        $this->authorize('view', $request->record());

        $record = $request->resource()
            ->displayQuery()->findOrFail($request->resourceId());

        $record->loadMissing($request->getWith());

        return $this->response(
            $request->toResponse($record)
        );
    }

    /**
     * Update resource record in storage.
     */
    public function update(UpdateResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof HasOperations, 404);

        // Resourceful update flag
        $this->authorize('update', $request->record());

        $request->performValidation();

        Db::transaction(function () use ($request) {
            $record = $request->resource()->update(
                $request->hydrateModel($request->record()), $request
            );

            $this->handleCustomAssociatedResources($record, $request);
        });

        return $this->response(
            $request->toResponse($request->resource()->displayQuery()
                ->with($request->getWith())
                ->find($request->resourceId()))
        );
    }

    /**
     * Remove resource record from storage.
     */
    public function destroy(ResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof HasOperations, 404);

        // Resourceful destroy flag
        $this->authorize('delete', $request->record());

        $content = DB::transaction(function () use ($request) {
            return $request->resource()->delete($request->record());
        });

        return $this->response($content, empty($content) ? 204 : 200);
    }

    /**
     * Sync the given record associations.
     */
    protected function handleCustomAssociatedResources(Model $record, ResourceRequest $request): Model
    {
        if ($request->resource()->isAssociateable()) {
            $associations = $this->filterAssociations(
                $request->resource(),
                $request->associateables()
            );

            $this->syncAssociations($request->resource(), $record->getKey(), $associations);
        }

        return $record;
    }
}
