<?php

namespace Modules\Lists\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Lists\Database\factories\ListModelFactory;

use Modules\Core\VisibilityGroup\HasVisibilityGroups;
use Modules\Core\VisibilityGroup\RestrictsModelVisibility;

class ListModel extends Model implements HasVisibilityGroups {

    use HasFactory, RestrictsModelVisibility;

    protected $fillable = ['name', 'description'];

    protected $table = 'lists';

    
    protected static function newFactory()
    {
        return ListModelFactory::new();
    }
}
