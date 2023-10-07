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

namespace Modules\Core\Updater\Exceptions;

use Exception;

class HasWrongPermissionsException extends UpdaterException
{
    /**
     * Initialize new HasWrongPermissionsException instance
     *
     * @param  string  $message
     * @param  int  $code
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct('Incorrect files permissions.', 500, $previous);
    }
}
