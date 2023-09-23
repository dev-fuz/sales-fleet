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

namespace Modules\Contacts\Fields;


use Modules\Lists\Models\ListModel;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Lists\Http\Resources\ListResource;

class ListField extends BelongsTo {


    /**
     * Create new instance of Source field
     *
     * @param  string  $label Custom label
     */
    public function __construct($label = null)
    {
        parent::__construct('list', ListModel::class, $label ?? "List");

        $this->setJsonResource(ListResource::class)
            ->options(Innoclapps::resourceByModel(ListModel::class))
            ->acceptLabelAsValue();
    }
}