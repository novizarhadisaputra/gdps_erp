<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Disable database persistence.
     */
    public $timestamps = false;

    /**
     * Indicate that the model exists.
     */
    public $exists = true;

    /**
     * Get the value of the primary key.
     */
    public function getKey()
    {
        return $this->id;
    }
}
