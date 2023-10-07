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

use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends ApiController
{
    /**
     * Export resource data
     */
    public function handle(ResourceRequest $request): BinaryFileResponse
    {
        abort_unless($request->resource() instanceof Exportable, 404);

        $this->authorize('export', $request->resource()::$model);

        $fields = $request->resource()
            ->fieldsForExport()
            ->when(is_array($request->fields), function ($fields) use ($request) {
                return $fields->filter(fn (Field $field) => $field->isPrimary() || in_array($field->attribute, $request->fields));
            });

        return $request->resource()
            ->exportable($request->resource()->exportQuery($request, $fields))
            ->setFields($fields)
            ->download($request->type);
    }
}
