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

use Modules\Core\Table\Column;

class ID extends Field
{
    /**
     * Field component.
     */
    public static $component = '';

    /**
     * Initialize new ID instance.
     */
    public function __construct(string $attribute = 'id', string $label = null)
    {
        parent::__construct($attribute, $label ?: __('core::app.id'));

        $this->onlyOnIndex()
            ->excludeFromImport()
            ->readOnly(true)
            ->tapIndexColumn(fn (Column $column) => $column
                ->minWidth('120px')
                ->width('120px')
                ->centered()
            );
    }

    /**
     * Resolve the field value for export
     *
     * @param  \Modules\Core\Models\Model  $model
     */
    public function resolveForExport($model)
    {
        return null;
    }
}
