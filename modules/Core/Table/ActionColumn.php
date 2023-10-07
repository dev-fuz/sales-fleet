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

namespace Modules\Core\Table;

class ActionColumn extends Column
{
    public bool $sortable = false;

    public bool $customizeable = false;

    public string $attribute = 'actions';

    public ?string $label = null;

    /**
     * Initialize new ActionColumn instance.
     */
    public function __construct()
    {
        $this->minWidth('50px')->width('50px');
    }
}
