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

namespace Modules\Contacts\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Users\Criteria\QueriesByUserCriteria;

class ViewAuthorizedCompaniesCriteria implements QueryCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        /** @var \Modules\Users\Models\User */
        $user = Auth::user();

        $query->unless($user->can('view all companies'), function ($query) use ($user) {
            $query->where(function ($query) use ($user) {
                $query->criteria(new QueriesByUserCriteria($user));

                if ($user->can('view team companies')) {
                    $query->orWhereHas('user.teams', function ($query) use ($user) {
                        $query->where('teams.user_id', $user->getKey());
                    });
                }
            });
        });
    }
}
