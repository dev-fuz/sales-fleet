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

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Resources\ImportResource;
use Modules\Core\Models\Import;
use Modules\Core\Resource\Import\RowsExceededException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportController extends ApiController
{
    /**
     * Get the import files in storage for the resource.
     */
    public function index(ResourceRequest $request): AnonymousResourceCollection
    {
        abort_unless($request->resource() instanceof Importable, 404);

        return ImportResource::collection(
            Import::with('user')->byResource($request->resource()->name())->latest()->get()
        );
    }

    /**
     * Perform import for the current resource.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $import = Import::findOrFail($request->route('id'));

        if (! $import->nextBatch()) {
            $request->validate([
                'mappings' => 'required|array',
                'mappings.*.attribute' => 'nullable|distinct|string',
                'mappings.*.auto_detected' => 'required|boolean',
                'mappings.*.original' => 'required|string',
                'mappings.*.skip' => 'required|boolean',
                'mappings.*.detected_attribute' => 'present',
            ]);

            // Update with the user provided mappings
            $import->data['mappings'] = $request->mappings;

            $import->save();
        }

        $this->increasePhpIniValues();

        try {
            $request->resource()->importable()->perform($import);

            return $this->response(new ImportResource($import->loadMissing('user')));
        } catch (Exception|RowsExceededException $e) {
            if ($e instanceof RowsExceededException) {
                $deleted = $import->delete();
            }

            return $this->response([
                'message' => $e->getMessage(),
                'deleted' => $deleted ?? false,
                'rows_exceeded' => $e instanceof RowsExceededException,
            ], 500);
        }
    }

    /**
     * Initiate new import and start mapping.
     */
    public function upload(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $request->validate(['file' => 'required|mimes:csv,txt']);

        $import = $request->resource()
            ->importable()
            ->upload(
                $request->file('file'),
                $request->user()
            );

        return $this->response(new ImportResource($import->loadMissing('user')));
    }

    /**
     * Download sample import file.
     */
    public function sample(ResourceRequest $request): BinaryFileResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $totalRows = $request->get('total_rows', 1);

        return $request->resource()->importSample($totalRows)->download();
    }

    /**
     * Revert the import.
     *
     * The request must be made in batches until there are no records associated.
     */
    public function revert(ResourceRequest $request)
    {
        $import = Import::findOrFail($request->route('id'));

        abort_unless(404, $import->isRevertable());

        $this->authorize('revert', $import);

        $this->increasePhpIniValues();

        $reverted = 0;

        if (app()->isProduction()) {
            DB::disableQueryLog();
        }

        ChangeLogger::disable();

        foreach ($request->resource()
            ->newQuery()
            ->where('import_id', $import->getKey())
            ->withCommon()
            ->limit($request->input('limit', 500))
            ->get() as $model) {
            if ($model->usesSoftDeletes()) {
                $request->resource()->forceDelete($model);
            } else {
                $request->resource()->delete($model);
            }

            if ($import->imported > 0) {
                $import->imported--;
            }

            $reverted++;
        }

        if (app()->isProduction()) {
            DB::enableQueryLog();
        }

        $import->save();

        return $this->response(new ImportResource($import->loadMissing('user')));
    }

    /**
     * Delete the given import.
     */
    public function destroy(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $import = Import::findOrFail($request->route('id'));

        $this->authorize('delete', $import);

        $import->delete();

        return $this->response('', 204);
    }

    protected function increasePhpIniValues(): void
    {
        if (! app()->runningUnitTests()) {
            \DetachedHelper::raiseMemoryLimit('256M');
            @ini_set('max_execution_time', 300);
        }
    }
}
