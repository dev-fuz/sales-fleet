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

namespace Modules\Contacts\Fields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Arr;
use Modules\Contacts\Http\Resources\CompanyResource;
use Modules\Contacts\Resource\Company\Company;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\ConfiguresOptions;
use Modules\Core\Fields\MorphToMany;
use Modules\Core\Fields\Selectable;
use Modules\Core\HasOptions;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Table\MorphToManyColumn;

class Companies extends MorphToMany
{
    use ConfiguresOptions, HasOptions, Selectable;

    /**
     * Multi select custom component
     */
    public static $component = 'select-multiple-field';

    public ?int $order = 1001;

    protected static Company $resource;

    /**
     * Create new instance of Companies field
     *s
     *
     * @param  string  $companies
     * @param  string  $label Custom label
     */
    public function __construct($relation = 'companies', $label = null)
    {
        parent::__construct($relation, $label ?? __('contacts::company.companies'));

        static::$resource = Innoclapps::resourceByName('companies');

        $this->labelKey('name')
            ->valueKey('id')
            // Used for export
            ->displayUsing(
                fn ($model) => (string) $model->companies->pluck('displayName')->implode(', ')
            )
            ->onOptionClickRedirectTo('/companies/{id}')
            ->eachOnNewLine()
            ->excludeFromZapierResponse()
            ->async('/companies/search')
            ->lazyLoad('/companies', ['order' => 'created_at|desc'])
            ->provideSampleValueUsing(fn () => 'Company Name, Other Company Name')
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value) {
                return ! is_null($value) ? $this->fillCallback($model, $this->parseValue($value, $request)) : null;
            });
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): MorphToManyColumn
    {
        return new MorphToManyColumn(
            $this->morphToManyRelationship,
            $this->labelKey,
            $this->label
        );
    }

    /**
     * Resolve the value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if ($model->relationLoaded($this->morphToManyRelationship)) {
            return [
                $this->attribute => CompanyResource::collection(
                    $this->resolve($model)
                ),
            ];
        }
    }

    /**
     * Parse the given value for storage.
     */
    protected function parseValue($value, ResourceRequest $request): Collection
    {
        // Perhaps int e.q. when ID provided?
        $value = is_string($value) ? explode(',', $value) : Arr::wrap($value);
        $collection = new Collection([]);

        foreach ($value as $id) {
            if ($model = $this->getModelFromValue($id, $request)) {
                $collection->push($model);
            }
        }

        return $collection;
    }

    /**
     * Get model instance from the given ID and ensure it's authorized to view before syncing.
     */
    protected function getModelFromValue(int|string|null $value, ResourceRequest $request): ?EloquentModel
    {
        $model = null;

        // ID provided?
        if (is_numeric($value)) {
            $model = static::$resource->newQuery()->find($value);
        } elseif ($value) {
            $model = static::$resource->findByName(trim($value), static::$resource->newQueryWithTrashed());

            if ($model?->trashed()) {
                $model->restore();
            }
        }

        return $model && $request->user()->can('view', $model) ? $model : null;
    }

    /**
     * Get the fill callback.
     */
    protected function fillCallback(Model $model, Collection $ids)
    {
        return function () use ($model, $ids) {
            if ($model->wasRecentlyCreated) {
                if (count($ids) > 0) {
                    $model->{$this->morphToManyRelationship}()->attach($ids);
                }
            } else {
                $model->{$this->morphToManyRelationship}()->sync($ids);
            }
        };
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'labelKey' => $this->labelKey,
            'valueKey' => $this->valueKey,
        ]);
    }
}
