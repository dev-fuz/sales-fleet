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

namespace Modules\Core\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Table\Table;

class TableRequestCriteria extends RequestCriteria
{
    /**
     * Initialize new TableRequestCriteria instance.
     */
    public function __construct(protected Collection $columns, protected Table $table)
    {
        parent::__construct();
    }

    /**
     * Apply order for the current request.
     *
     * @param  mixed  $order
     * @return void
     */
    protected function applyOrder($order, Builder $query): Builder
    {
        // No order applied
        if (empty($order)) {
            return $query;
        }

        // Remove any default order
        $query->reorder();

        collect($order)
            ->map(fn ($data) => [
                'column' => $this->table->getColumn($data['attribute']),
                'direction' => ($data['direction'] ?? '') ?: 'asc',
            ])
            ->reject(
                fn ($data) => is_null($data['column'])
            )
            ->each(function ($data) use (&$query) {
                $query = $data['column']->orderBy($query, $data['direction']);
            });

        return $query;
    }
}
