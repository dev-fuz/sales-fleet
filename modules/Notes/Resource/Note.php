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

namespace Modules\Notes\Resource;

use Modules\Comments\Contracts\HasComments;
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Criteria\RelatedCriteria;
use Modules\Core\Fields\Editor;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Notes\Http\Resources\NoteResource;

class Note extends Resource implements HasComments, HasOperations
{
    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Notes\Models\Note';

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return NoteResource::class;
    }

    /**
     * Provide the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            Editor::make('body')->rules(['required', 'string'])->onlyOnForms(),
        ];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): ?string
    {
        if (! auth()->user()->isSuperAdmin()) {
            return RelatedCriteria::class;
        }

        return null;
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'notes';
    }

    /**
     * Get the relations to eager load when quering associated records
     */
    public function withWhenAssociated(): array
    {
        return ['user'];
    }

    /**
     * Get the countable relations when quering associated records
     */
    public function withCountWhenAssociated(): array
    {
        return ['comments'];
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'via_resource' => ['required', 'in:contacts,companies,deals', 'string'],
            'via_resource_id' => ['required', 'numeric'],
        ];
    }

    /**
     * Get the custom validation messages for the resource
     */
    public function validationMessages(): array
    {
        return [
            'body.required' => __('validation.required_without_label'),
        ];
    }
}
