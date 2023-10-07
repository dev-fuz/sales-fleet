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

namespace Modules\Core\Fields;

use Illuminate\Support\Collection;

class FieldsCollection extends Collection
{
    /**
     * Filter only the authorized fields.
     */
    public function authorized(): static
    {
        return $this->filter->authorizedToSee()->values();
    }

    /**
     * Find field by the given attribute.
     */
    public function find(string $attribute): ?Field
    {
        return $this->firstWhere('attribute', $attribute);
    }

    /**
     * Find field by the given request attribute.
     */
    public function findByRequestAttribute(string $attribute): ?Field
    {
        return $this->first(fn ($field) => $field->requestAttribute() === $attribute);
    }

    /**
     * Filter the fields for creation.
     */
    public function filterForCreation(): static
    {
        return $this->filter(function (Field $field) {
            return $field->isApplicableForCreation();
        })->values();
    }

    /**
     * Filter fields only visible on create view.
     */
    public function visibleOnCreate(): static
    {
        return $this->reject(fn (Field $field) => $field->showOnCreation === false)->values();
    }

    /**
     * Filter the fields for update.
     */
    public function filterForUpdate(): static
    {
        return $this->filter(function (Field $field) {
            return $field->isApplicableForUpdate();
        })->values();
    }

    /**
     * Filter fields only visible on update view.
     */
    public function visibleOnUpdate(): static
    {
        return $this->reject(fn (Field $field) => $field->showOnUpdate === false)->values();
    }

    /**
     * Filter the fields for detail.
     */
    public function filterForDetail(): static
    {
        return $this->filter(function (Field $field) {
            return $field->isApplicableForDetail();
        })->values();
    }

    /**
     * Filter fields only visible on detail view.
     */
    public function visibleOnDetail(): static
    {
        return $this->reject(fn (Field $field) => $field->showOnDetail === false)->values();
    }

    /**
     * Filter the fields for index.
     */
    public function filterForIndex(): static
    {
        return $this->filter(function (Field $field) {
            return $field->isApplicableForIndex();
        })->values();
    }

    /**
     * Filter the fields for import sample.
     */
    public function filterForImportSample(): static
    {
        return $this->filterForImport()->reject(function (Field $field) {
            return $field->excludeFromImportSample;
        });
    }

    /**
     * Filter the fields for placeholders.
     */
    public function filterForPlaceholders(): static
    {
        return $this->reject(
            fn (Field $field) => is_null($field->mailableTemplatePlaceholder(null))
        )->values();
    }

    /**
     * Filter the fields for import.
     */
    public function filterForImport(): static
    {
        return $this->reject(function (Field $field) {
            return $field->excludeFromImport;
        })->withoutReadonly()->values();
    }

    /**
     * Filter the fields for export.
     */
    public function filterForExport(): static
    {
        return $this->reject(function (Field $field) {
            return $field->excludeFromExport;
        })->values();
    }

    /**
     * Disable inline edit for all the fields in the collection.
     */
    public function disableInlineEdit(): static
    {
        $this->each->disableInlineEdit();

        return $this;
    }

    /**
     * Apply a filter to remove fields excluded from Zapier.
     */
    public function withoutZapierExcluded(): static
    {
        return $this->reject(
            fn (Field $field) => $field->excludeFromZapierResponse && request()->isZapier()
        )->values();
    }

    /**
     * Apply a filter to exclude the readonly fields from the collection.
     */
    public function withoutReadonly(): static
    {
        return $this->filter(function (Field $field) {
            return ! $field->isReadOnly();
        });
    }
}
