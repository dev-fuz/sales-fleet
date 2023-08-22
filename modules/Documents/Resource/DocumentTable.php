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

namespace Modules\Documents\Resource;

use Modules\Core\Actions\ForceDeleteAction;
use Modules\Core\Actions\RestoreAction;
use Modules\Core\Table\Table;

class DocumentTable extends Table
{
    /**
     * Indicates whether the user can customize columns orders and visibility
     */
    public bool $customizeable = true;

    /**
     * Provide the attributes that should be appended within the response
     */
    protected function appends(): array
    {
        return ['public_url'];
    }

    /**
     * Additional fields to be selected with the query
     */
    public function addSelect(): array
    {
        return [
            'uuid', // for public_url append
            'user_id', // user_id is for the policy checks
            'status', // for showing the dropdown send document item
        ];
    }

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
     * Boot table
     */
    public function boot(): void
    {
        $this->orderBy('created_at', 'desc');
    }
}
