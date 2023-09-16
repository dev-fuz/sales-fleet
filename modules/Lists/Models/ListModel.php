<?php

namespace Modules\Lists\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Lists\Database\factories\ListModelFactory;


class ListModel extends Model {

    use HasFactory;

    protected $fillable = ['name', 'description'];

    protected $table = 'lists';

    
    protected static function newFactory()
    {
        return ListModelFactory::new();
    }
}
