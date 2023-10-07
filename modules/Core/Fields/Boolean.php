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

use Illuminate\Support\Arr;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Table\Column;

class Boolean extends Field implements Customfieldable
{
    /**
     * Field component.
     */
    public static $component = 'boolean-field';

    /**
     * Checkbox checked value
     */
    public mixed $trueValue = true;

    /**
     * Checkbox unchecked value
     */
    public mixed $falseValue = false;

    /**
     * Initialize new Boolean instance.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->provideSampleValueUsing(fn () => is_bool($this->trueValue) ?
            Arr::random([$this->trueValue, 1, 'true']) :
            $this->trueValue
        );
    }

    /**
     * Get the field index column.
     */
    public function indexColumn(): Column
    {
        return parent::indexColumn()->centered();
    }

    /**
     * Checkbox checked value
     */
    public function trueValue(mixed $val): static
    {
        $this->trueValue = $val;

        return $this;
    }

    /**
     * Checkbox unchecked value
     */
    public function falseValue(mixed $val): static
    {
        $this->falseValue = $val;

        return $this;
    }

    /**
     * Resolve the field value for export
     * The export value should be in the original database value
     * not e.q. Yes or No
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return string|null
     */
    public function resolveForExport($model)
    {
        return $this->resolve($model);
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        $value = parent::resolveForDisplay($model);

        return $value === $this->trueValue ? __('core::app.yes') : __('core::app.no');
    }

    /**
     * Create the custom field value column in database
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @param  string  $fieldId
     * @return void
     */
    public static function createValueColumn($table, $fieldId)
    {
        $table->boolean($fieldId)->nullable()->default(false);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'trueValue' => $this->trueValue,
            'falseValue' => $this->falseValue,
        ]);
    }
}
