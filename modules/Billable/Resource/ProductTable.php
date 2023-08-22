<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.2.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Billable\Resource;

use Modules\Core\Actions\ForceDeleteAction;
use Modules\Core\Actions\RestoreAction;
use Modules\Core\Table\Table;

class ProductTable extends Table
{
    /**
     * Get the actions intended for the trashed table
     *
     * NOTE: No authorization is performed on these action, all actions will be visible to the user
     */
    public function actionsForTrashedTable(): array
    {
        return [new RestoreAction, new ForceDeleteAction];
    }

    /**
     * Additional fields to be selected with the query
     */
    public function addSelect(): array
    {
        return [
            'created_by', // created_by is for the policy checks
        ];
    }

    /**
     * Boot table
     */
    public function boot(): void
    {
        $this->orderBy('is_active', 'desc')->orderBy('name', 'asc');
    }
}
