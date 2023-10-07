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

namespace Modules\Core\Table;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Core\Contracts\Presentable;
use Modules\Core\Models\Model;

/** @mixin \Modules\Core\Table\Table */
trait ParsesResponse
{
    /**
     * Parse the response for the request.
     */
    protected function parseResponse(LengthAwarePaginator $result): LengthAwarePaginator
    {
        $columns = $this->getUserColumns()->filter->shouldQuery();

        $result->getCollection()->transform(function (Model $model) use ($columns) {
            $columns->filter->isRelation()->each(function (Column $column) use ($model) {
                $this->appendAttributesWhenRelation($column, $model);
            });

            return $this->createRow($model, $columns);
        });

        $this->tapResponse($result);

        return $result;
    }

    /**
     * Create new row for the response.
     */
    protected function createRow(Model $model, Collection $columns): array
    {
        $row = ['id' => $model->getKey()];

        $columns->each(function (Column $column) use ($model, &$row) {
            $row += $model->only($this->getAppendedAttributesForColumn($column, $model));

            $row['authorizations'] = $this->getAuthorizations($model);

            $this->addDisabledFieldsForEditToRow($row, $column, $model);

            $column->fillRowData($row, $model);

            $this->addCountedRelationshipsToRow($row, $model);
        });

        return $row;
    }

    protected function getAppendedAttributesForColumn(Column $column, Model $model): array
    {
        return array_merge(
            $this->appends,
            $column->appends,
            $model instanceof Presentable ? ['path'] : []
        );
    }

    /**
     * Tap the table response.
     */
    protected function tapResponse(LengthAwarePaginator $response): void
    {
    }

    /**
     * Add the disabled fields data to the row.
     */
    protected function addDisabledFieldsForEditToRow(array &$row, Column $column, Model $model): void
    {
        $field = $column->field;

        if (! $field) {
            return;
        }

        if (! array_key_exists('_edit_disabled', $row)) {
            $row['_edit_disabled'] = [];
        }

        // When the field is not applicable for update and has custom inline edit field
        // we will assume that we wanted this field to be applicable for update as added custom inline edit field(s)
        $row['_edit_disabled'][$field->attribute] = with($field, function ($field) use ($model) {
            if (! $field->isApplicableForUpdate() && is_null($field->inlineEditField())) {
                return true;
            }

            return $field->isInlineEditDisabled($model);
        });
    }

    /**
     * Add the counted relationship to the row, including the counted without columns.
     */
    protected function addCountedRelationshipsToRow(array &$row, Model $model): void
    {
        foreach ($this->getCountedRelationships() as $key => $relation) {
            if (is_string($key)) {
                $relation = $key;
            } elseif (str_contains($relation, ' as ')) {
                $relation = Str::after($relation, 'as ');
            }

            $attribute = $relation.'_count';

            if (! array_key_exists($attribute, $row)) {
                $row[$attribute] = $model->{$attribute};
            }
        }
    }

    /**
     * Append attributes when column is relation.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function appendAttributesWhenRelation(RelationshipColumn $column, $model): void
    {
        if ($column instanceof BelongsToColumn && $model->{$column->relationName}) {
            $this->appendAttributesWhenBelongsTo($column, $model);
        } elseif (($column instanceof HasManyColumn || $column instanceof BelongsToManyColumn)) {
            $this->appendAttributesWhenHasManyOrBelongsToMany($column, $model);
        }
    }

    /**
     * Append attributes when BelongsToColumn.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function appendAttributesWhenBelongsTo(BelongsToColumn $column, $model): void
    {
        $model->{$column->relationName}->append(array_merge(
            $column->appends,
            $model->{$column->relationName}()->getModel() instanceof Presentable ? ['path', 'display_name'] : []
        ));
    }

    /**
     * Append attributes when HasManyColumn.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function appendAttributesWhenHasManyOrBelongsToMany(HasManyColumn|BelongsToManyColumn $column, $model): void
    {
        $model->{$column->relationName}->map(function ($relationModel) use ($column) {
            unset($relationModel->pivot);

            return $relationModel->append(array_merge(
                $column->appends,
                $relationModel instanceof Presentable ? ['path', 'display_name'] : [],
            ));
        });
    }
}
