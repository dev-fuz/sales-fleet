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

/** @mixin \Modules\Billable\Models\BillableProduct */
class BillableProductResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'product_id' => $this->product_id,
            'name' => $this->name,
            'description' => $this->description,
            'unit_price' => (float) $this->unit_price,
            'qty' => (float) $this->qty,
            'unit' => $this->unit,
            'tax_rate' => (float) $this->tax_rate,
            'tax_label' => $this->tax_label,
            'discount_type' => $this->discount_type,
            'discount_total' => (float) $this->discount_total,
            'sku' => $this->sku,
            'amount' => $this->totalAmountWithDiscount(),
            'note' => $this->note,
            'display_order' => $this->display_order,
        ], $request);
    }
}
