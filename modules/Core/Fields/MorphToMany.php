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

abstract class MorphToMany extends Field
{
    /**
     * Field relationship name
     */
    public string $morphToManyRelationship;

    /**
     * Initialize new MorphToMany instance class
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->morphToManyRelationship = $attribute;

        $this->fillUsing(function () {
        });
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     */
    public function mailableTemplatePlaceholder($model)
    {
        return null;
    }

    /**
     * Check whether the field is excluded from index query.
     */
    public function isExcludedFromIndexQuery(): bool
    {
        return true;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'morphToManyRelationship' => $this->morphToManyRelationship,
        ]);
    }
}
