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

namespace Modules\Core\Support\Macros\Request;

class IsSearching
{
    /**
     * Determine whether user is performing search via the RequestCriteria
     *
     * @return bool
     */
    public function __invoke()
    {
        return ! is_null(request()->get('q', null));
    }
}
