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

namespace Modules\Core\Menu\Highlights;

use JsonSerializable;

abstract class Highlight implements JsonSerializable
{
    /**
     * Get the highlight name.
     */
    abstract public function name(): string;

    /**
     * Get the highligh count.
     */
    abstract public function count(): int;

    /**
     * Get the background color variant when the highlight count is bigger then zero
     */
    abstract public function backgroundColorVariant(): string;

    /**
     * Get the front-end route that the highly will redirect to.
     */
    abstract public function route(): array|string;

    /**
     * Prepare the class for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return [
            'count' => $this->count(),
            'name' => $this->name(),
            'route' => $this->route(),
            'backgroundColorVariant' => $this->backgroundColorVariant(),
        ];
    }
}
