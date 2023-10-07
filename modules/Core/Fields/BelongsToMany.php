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

use Modules\Core\Table\BelongsToManyColumn;

/**
 * Currently used only for INDEX, not working on forms or other views.
 */
class BelongsToMany extends Optionable
{
    /**
     * Field component.
     */
    public static $component = 'select-multiple-field';

    /**
     * Field relationship name
     */
    public string $belongsToManyRelationship;

    /**
     * Initialize new HasMany instance class
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->belongsToManyRelationship = $attribute;

        $this->fillUsing(function () {
        });
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): BelongsToManyColumn
    {
        return new BelongsToManyColumn(
            $this->belongsToManyRelationship,
            $this->labelKey,
            $this->label
        );
    }
}
