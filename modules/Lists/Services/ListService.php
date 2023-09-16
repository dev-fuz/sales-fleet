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
    public function update(array $attributes, ListModel $listModel): ListModel
    {
        $listModel->fill($attributes)->save();
        return $listModel;
    }
}