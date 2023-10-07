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

namespace Modules\Core\Resource;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Concerns\UserOrderable;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Criteria\ExportRequestCriteria;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\HasMany;
use Modules\Core\Fields\MorphMany;
use Modules\Core\Fields\MorphToMany;
use Modules\Core\Fields\RelationshipCount;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Models\PinnedTimelineSubject;

/**
 * @mixin \Modules\Core\Resource\Resource
 */
trait QueriesResources
{
    /**
     * Get a new query builder for the resource's model table.
     */
    public function newQuery(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Get a new query builder for the resource's model table that includes trashed records.
     */
    public function newQueryWithTrashed(): Builder
    {
        return $this->newModel()->withTrashed();
    }

    /**
     * Prepare display query.
     */
    public function displayQuery(): Builder
    {
        $query = $this->with($this->newQuery(), $this->resolveFields());

        return $query->withCommon();
    }

    /**
     * Prepare index query.
     */
    public function indexQuery(ResourceRequest $request): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        if ($request->missing(RequestCriteria::ORDER_KEY)) {
            $this->applyDefaultOrder($query);
        }

        $query->criteria([
            $this->getFiltersCriteria($request, 'filters'),
            $this->getRequestCriteria($request),
        ]);

        return $this->with($query, $this->fieldsForIndexQuery());
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        $query->criteria($this->getRequestCriteria($request));

        return $this->applyDefaultOrder($query);
    }

    /**
     * Prepare search query.
     */
    public function searchQuery(ResourceRequest $request): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        $query->criteria($this->getRequestCriteria($request));

        if ($request->missing(RequestCriteria::ORDER_KEY)) {
            $this->applyDefaultOrder($query);
        }

        return $this->with($query, $this->resolveFields());
    }

    /**
     * Prepare the trashed query.
     */
    public function trashedQuery(): Builder
    {
        return $this->newQuery()->onlyTrashed();
    }

    /**
     * Prepare trashed index query.
     */
    public function trashedIndexQuery(ResourceRequest $request): Builder
    {
        return $this->indexQuery($request)->onlyTrashed();
    }

    /**
     * Prepare trashed display query.
     */
    public function trashedDisplayQuery(): Builder
    {
        return $this->displayQuery()->onlyTrashed();
    }

    /**
     * Prepare search query for trashed records.
     */
    public function trashedSearchQuery(ResourceRequest $request): Builder
    {
        return $this->searchQuery($request)->onlyTrashed();
    }

    /**
     * Prepare an export query.
     */
    public function exportQuery(ResourceRequest $request, FieldsCollection $fields = null): Builder
    {
        $query = $this->newQueryWithAuthorizedRecordsCriteria();

        $query->criteria(new ExportRequestCriteria($request));

        if ($request->filters) {
            $query->criteria($this->getFiltersCriteria($request, 'filters'));
        }

        return $this->with($query, $fields ?? $this->fieldsForExport());
    }

    /**
     * Prepare table query.
     */
    public function tableQuery(ResourceRequest $request): Builder
    {
        return $this->newQueryWithAuthorizedRecordsCriteria();
    }

    /**
     * Create new query with the authorized records criteria.
     */
    public function newQueryWithAuthorizedRecordsCriteria(): Builder
    {
        $query = $this->newQuery();

        if ($criteria = $this->viewAuthorizedRecordsCriteria()) {
            $query->criteria($criteria);
        }

        return $query;
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline.
     */
    public function timelineQuery(Model $subject, ResourceRequest $request): Builder
    {
        $query = $this->associatedIndexQuery($subject, $request, false)
            ->with('pinnedTimelineSubjects')
            ->withPinnedTimelineSubjects($subject)
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc');

        if ($query->getModel()->usesTimestamps()) {
            $query->orderBy($query->getModel()->getQualifiedCreatedAtColumn(), 'desc');
        }

        return $query;
    }

    /**
     * Create query when the resource is associated for index.
     */
    public function associatedIndexQuery(Model $primary, ResourceRequest $request, bool $latest = true): Builder
    {
        $model = $this->newModel();
        $relation = Innoclapps::resourceByModel($primary)->associateableName();

        return $this->newQuery()
            ->select($this->newModel()->prefixColumns())
            ->criteria($this->getRequestCriteria($request))
            ->with($this->withWhenAssociated())
            ->withCount($this->withCountWhenAssociated())
            ->whereHas($relation, function ($query) use ($primary) {
                return $query->where($primary->getKeyName(), $primary->getKey());
            })->when($latest && $model->usesTimestamps(), function ($instance) use ($model) {
                $instance->orderBy($model->getQualifiedCreatedAtColumn(), 'desc');
            });
    }

    /**
     * Apply the default order from the resource to the given query.
     */
    public function applyDefaultOrder(Builder $query): Builder
    {
        if (in_array(UserOrderable::class, class_uses_recursive(static::$model))) {
            return $query->userOrdered();
        } else {
            return $query->orderBy(static::$orderBy, static::$orderByDir);
        }
    }

    /**
     * Get the relations to eager load when quering associated records
     */
    public function withWhenAssociated(): array
    {
        return [];
    }

    /**
     * Get the countable relations when quering associated records
     */
    public function withCountWhenAssociated(): array
    {
        return [];
    }

    /**
     * Add "with" relations to the given query from the given fields.
     */
    public function with(Builder $query, $fields): Builder
    {
        $fields = $fields->withoutZapierExcluded();

        $relations = $fields->pluck('with')->flatten()
            ->merge($fields->whereInstanceOf(BelongsTo::class)->pluck('belongsToRelation'))
            ->merge($fields->whereInstanceOf(HasMany::class)->pluck('hasManyRelationship'))
            ->merge($fields->whereInstanceOf(MorphMany::class)->pluck('morphManyRelationship'))
            ->merge($fields->whereInstanceOf(MorphToMany::class)->pluck('morphToManyRelationship'))
            ->merge($fields->whereInstanceOf(Customfieldable::class)->filter(function ($field) {
                return $field->isCustomField() && $field->isOptionable();
            })->pluck('customField.relationName'))
            ->filter()
            ->unique();

        return $this->withCount($query->with($relations->all()), $fields);
    }

    /**
     * Add "with count" relations to the given query from the given fields.
     */
    public function withCount(Builder $query, $fields)
    {
        return $query->withCount(
            $fields->whereInstanceOf(RelationshipCount::class)
                ->pluck('countRelation')
                ->filter()
                ->unique()
                ->all()
        );
    }

    /**
     * Get the fields when creating index query
     */
    protected function fieldsForIndexQuery(): FieldsCollection
    {
        return $this->resolveFields()->reject->isExcludedFromIndexQuery();
    }
}
