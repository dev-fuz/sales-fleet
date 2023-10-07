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

namespace Modules\Contacts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Contacts\Database\Factories\PhoneFactory;
use Modules\Contacts\Enums\PhoneType;
use Modules\Core\CountryCallingCode;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Import\Import;

class Phone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => PhoneType::class,
    ];

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['phoneable'];

    /**
     * Get the phoneable
     */
    public function phoneable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate random phone number
     */
    public static function generateRandomNumber(): string
    {
        return CountryCallingCode::random().mt_rand(100, 1000).'-'.mt_rand(100, 1000).'-'.mt_rand(100, 1000);
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            // For table serialization, will show the string value on the front-end
            'type' => $this->type->name,
        ]);
    }

    /**
     * Touch the owning relations of the model.
     *
     * @return void
     */
    public function touchOwners()
    {
        if (Import::$running) {
            return;
        }

        return parent::touchOwners();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PhoneFactory
    {
        return PhoneFactory::new();
    }
}
