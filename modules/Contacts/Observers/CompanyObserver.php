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

namespace Modules\Contacts\Observers;

use Modules\Contacts\Models\Company;

class CompanyObserver
{
    /**
     * Handle the Contact "deleting" event.
     */
    public function deleting(Company $company): void
    {
        if ($company->isForceDeleting()) {
            $company->purge();
        }
    }
}
