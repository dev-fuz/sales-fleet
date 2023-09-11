<?php
namespace Modules\Lists\Services;


use Modules\Lists\Models\ListModel;

class ListService
{
    /**
     * Save the new list
     */
    public function create(array $attributes): ListModel
    {
        return ListModel::create($attributes);
    }

    /**
     * Update the given brand in storage.
     */
    // public function update(array $attributes, Brand $brand): Brand
    // {
    //     $brand->fill($attributes)->save();

    //     if ($attributes['visibility_group'] ?? false) {
    //         $brand->saveVisibilityGroup($attributes['visibility_group']);
    //     }

    //     if ($brand->wasChanged('is_default') && $brand->is_default === true) {
    //         $this->ensureNoOtherBrandIsDefaultThan($brand);
    //     }

    //     return $brand;
    // }
}