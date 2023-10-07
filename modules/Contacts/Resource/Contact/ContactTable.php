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

namespace Modules\Contacts\Resource\Contact;

use Modules\Core\Criteria\SearchByFirstNameAndLastNameCriteria;
use Modules\Core\Criteria\TableRequestCriteria;
use Modules\Core\Table\Table;

class ContactTable extends Table
{
    /**
     * Whether the table has actions column.
     */
    public bool $withActionsColumn = true;

    /**
     * Indicates whether the user can customize columns orders and visibility
     */
    public bool $customizeable = true;

    /**
     * Create new TableRequestCriteria criteria instance
     */
    protected function createTableRequestCriteria(): TableRequestCriteria
    {
        return (new TableRequestCriteria(
            $this->getUserColumns(),
            $this
        ))->appends(fn ($query) => $query->orWhere(function ($query) {
            $query->criteria(SearchByFirstNameAndLastNameCriteria::class);
        }));
    }

    /**
     * Boot table
     */
    public function boot(): void
    {
        $this->orderBy('created_at', 'desc');
    }
}
