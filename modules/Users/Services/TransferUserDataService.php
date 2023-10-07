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

namespace Modules\Users\Services;

use Modules\Core\Models\Filter;
use Modules\Core\Models\Workflow;
use Modules\Users\Events\TransferringUserData;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;

class TransferUserDataService
{
    protected int $fromUserId;

    /**
     * Create new TransferUserData instance.
     */
    public function __construct(protected int $toUserId, protected User $fromUser)
    {
        $this->fromUserId = $fromUser->getKey();
    }

    /**
     * Invoke the transfer
     */
    public function __invoke(): void
    {
        TransferringUserData::dispatch($this->toUserId, $this->fromUserId, $this->fromUser);

        $this->transferSharedFilters();
        $this->transferTeams();
        $this->transferWorkflows();
    }

    /**
     * Transfer shared filter.
     */
    public function transferSharedFilters(): void
    {
        Filter::where('user_id', $this->fromUserId)->shared()->update(['user_id' => $this->toUserId]);
    }

    /**
     * Transfer created workflows.
     */
    public function transferWorkflows(): void
    {
        Workflow::where('created_by', $this->fromUserId)->update(['created_by' => $this->toUserId]);
    }

    /**
     * Transfer teams.
     */
    public function transferTeams(): void
    {
        Team::where('user_id', $this->fromUserId)->update(['user_id' => $this->toUserId]);
    }
}
