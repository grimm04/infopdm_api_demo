<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="UnitItem",
 *      required={"unit_pembangkit_id", "name"}, 
 *      @SWG\Property(
 *          property="unit_pembangkit_id",
 *          description="unit_pembangkit_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class UnitItem extends Model
{

    public $connection = 'mysql-app'; /** Connection 2 */ 
    public $table = 'unit_item'; 

    public $fillable = [
        'unit_pembangkit_id',
        'name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'unit_pembangkit_id' => 'integer',
        'name' => 'string'
    ];

           /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [
        'name'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'unit_pembangkit_id' => 'required',
        'name' => 'required'
    ];

    public function unitPembangkitId()
    {
        return $this->belongsTo(\App\Models\UnitPembangkit::class, 'unit_pembangkit_id', 'id');
    }

    public function vibrasi() 
    {
        return $this->hasMany(\App\Models\Vibrasi::class, 'unit_item_id');
    }
    public function termografi() 
    {
        return $this->hasMany(\App\Models\Termografi::class, 'unit_item_id');
    }

}
