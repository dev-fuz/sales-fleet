<?php
namespace Modules\Lists\Services;


use Modules\Lists\Models\List;

class ListService
{
    /**
     * Save new brand in storage.
     */
    public function create(array $attributes): Brand
    {
        $brand = Brand::create($attributes);

        $brand->saveVisibilityGroup($attributes['visibility_group'] ?? []);

        if ($brand->is_default === true) {
            $this->ensureNoOtherBrandIsDefaultThan($brand);
        }

        return $brand;
    }

    /**
     * Update the given brand in storage.
     */
    public function update(array $attributes, Brand $brand): Brand
    {
        $brand->fill($attributes)->save();

        if ($attributes['visibility_group'] ?? false) {
            $brand->saveVisibilityGroup($attributes['visibility_group']);
        }

        if ($brand->wasChanged('is_default') && $brand->is_default === true) {
            $this->ensureNoOtherBrandIsDefaultThan($brand);
        }

        return $brand;
    }
}