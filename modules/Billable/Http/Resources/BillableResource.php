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

namespace Modules\Billable\Http\Resources;

use App\Http\Resources\ProvidesCommonData;
use Illuminate\Http\Request;
use Modules\Core\JsonResource;

/** @mixin \Modules\Billable\Models\Billable */
class BillableResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'tax_type' => $this->tax_type->name,
            'sub_total' => $this->sub_total,
            'has_discount' => $this->has_discount,
            'total_discount' => $this->total_discount,
            'total_tax' => $this->total_tax,
            'applied_taxes' => $this->getAppliedTaxes(),
            'total' => $this->total,
            // 'terms'    => $this->terms,
            // 'notes'    => $this->notes,
            'products' => $this->relationLoaded('products') ?
                BillableProductResource::collection($this->products) :
                [],
        ], $request);
    }
}
