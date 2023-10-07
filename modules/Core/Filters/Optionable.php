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

namespace Modules\Core\Filters;

use Modules\Core\HasOptions;

class Optionable extends Filter
{
    use HasOptions;

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            'options' => $this->resolveOptions(),
        ]);
    }
}
