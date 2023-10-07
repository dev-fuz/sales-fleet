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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use JsonSerializable;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Resources\JsonResource as BaseJsonResource;

class JsonResource extends BaseJsonResource
{
    /**
     * Top level resource actions
     *
     * @var null|\Illuminate\Support\Collection|array
     */
    protected $actions;

    /**
     * Provide common data for the resource
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    protected function withCommonData(array $data, $request): array
    {
        $data = parent::withCommonData($data, $request);

        if ($resource = Innoclapps::resourceByModel($this->resource)) {
            $this->mergeFieldsFromResource($resource, $data);
        }

        if (! $request->isZapier()) {
            $data[] = $this->mergeWhen(! is_null($this->actions), [
                'actions' => $this->actions,
            ]);

            if ($this->shouldMergeAssociations()) {
                $data[] = $this->merge([
                    'associations' => $this->prepareAssociationsForResponse(),
                ]);
            }
        }

        return $data;
    }

    /**
     * Merge the request fields in the resource
     */
    protected function mergeFieldsFromResource(Resource $resource, array &$data)
    {
        $fields = $resource->getFieldsForJsonResource(
            $this->resource,
            $this->userCanViewCurrentResource()
        );

        foreach ($fields as $field) {
            $data[] = $this->merge($field->resolveForJsonResource($this->resource));
        }
    }

    /**
     * Check whether the current user can see the current resource.
     */
    protected function userCanViewCurrentResource(): bool
    {
        // The wasRecentlyCreated check is performed for new main resource
        // e.q. in the front end the main resource is still shown after is created
        // to the user even if don't have permissions to view
        // e.q. regular user created a company and assigned to another user
        // after the company is assigned to another user, the creator won't
        // be able to see the company directly after creation
        // Because the front end still shows the fully create company after creation
        // In this case, to prevent showing 403 immediately we show the user the entity but after he navigated from
        // the entity view or tried to update, it will show the 403 error
        if ($this->wasRecentlyCreated) {
            return true;
        }

        /** @var \Modules\Users\Models\User */
        $user = Auth::user();

        return $user->can('view', $this->resource);
    }

    /**
     * Set top level resource actions (only for single models)
     *
     * @param  \Illuminate\Support\Collection|array  $actions
     * @return static
     */
    public function withActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Get the resource associations
     */
    protected function prepareAssociationsForResponse(): Collection
    {
        return collect($this->resource->associatedResources())
            ->map(function ($resourceRecords) {
                return $resourceRecords->map(function ($record, $index) {
                    // Only included needed data for the front-end
                    // if needed via API, users can use the ?with= parameter to load associated resources
                    return [
                        'id' => $record->id,
                        'display_name' => $record->display_name,
                        'path' => $record->path,
                        'is_primary_associated' => $index === 0,
                    ];
                });
            });
    }

    /**
     * Check whether a resource has associations and should be merged
     *
     * Associations are merged only if they are previously eager loaded
     */
    protected function shouldMergeAssociations(): bool
    {
        if (! method_exists($this->resource, 'associatedResources')) {
            return false;
        }

        return $this->resource->associationsLoaded();
    }

    /**
     * Resolve the resource to an array.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array
     */
    public function resolve($request = null)
    {
        $data = $this->toArray(
            $request = $request ?: app(ResourceRequest::class)
        );

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        return $this->filter((array) $data);
    }

    /**
     * Transform the resource into an HTTP response.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($request = null)
    {
        return $this->toResponse(
            $request ?: app(ResourceRequest::class)
        );
    }

    /**
     * Prepare the resource for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->resolve(app(ResourceRequest::class));
    }
}
