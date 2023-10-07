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

namespace Modules\Contacts\Resource;

use Modules\Contacts\Http\Resources\IndustryResource;
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Fields\Text;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;

class Industry extends Resource implements HasOperations
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Contacts\Models\Industry';

    /**
     * Set the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Text::make('name', __('contacts::company.industry.industry'))->rules(['required', 'string', 'max:191'])->unique(static::$model),
        ];
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('contacts::company.industry.industry');
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('contacts::company.industry.industries');
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return IndustryResource::class;
    }
}
