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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\Support\Microsoft\Client;

/**
 * @mixin \Modules\Core\Support\Microsoft\Client
 */
class MsGraph extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
