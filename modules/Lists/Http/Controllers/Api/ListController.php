<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.2.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Lists\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;



use Modules\Lists\Http\Requests\ListRequest;
use Modules\Lists\Models\ListModel;
use Modules\Lists\Services\ListService;
use Modules\Lists\Http\Resources\ListResource;


class ListController extends ApiController
{
    /**
     * Display a list.
     */
    public function index(): Mixed
    {
        $lists = ListModel::all();

        return $this->response(ListResource::collection($lists));
    }

    /**
     * Display the specified list.
     */
    public function show($id): JsonResponse
    {
        $list = ListModel::findOrFail($id);
        return $this->response(new ListResource($list));
    }

    /**
     * Store a newly created list in storage.
     */
    public function store(ListRequest $request, ListService $service): JsonResponse
    {

        $this->authorize('create', ListModel::class);

        $list = $service->create($request->input());

        return $this->response(
            new ListResource($list),
            201
        );
    }

    /**
     * Update the specified list.
     */
    public function update(ListModel $list, ListRequest $request, ListService $service): JsonResponse
    {
        $this->authorize('update', $list);

        $list = $service->update($request->input(), $list);

        $list->loadMissing($request->getWith());

        return $this->response(
            new ListResource($list)
        );
    }

    /**
     * Remove the specified list from storage.
     */
    public function destroy($id)
    {
        $list = ListModel::findOrFail($id);
        if($list){
            $list->delete();
            return $this->response('', 204);
        } else {
            abort(409, 'There must be at least one list.');
        }
    }
}
