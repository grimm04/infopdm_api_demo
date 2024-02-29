<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="Role",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
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
 *          property="status",
 *          description="status",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="level",
 *          description="level",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="privilages",
 *          description="privilages",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="description",
 *          description="description",
 *          type="string"
 *      )
 * )
 */
class Role extends Model
{
    public $table = 'roles';
    protected $maps =[ 
        'id_unit_pembangkit' => 'unit_pembangkit_id',  
    ];
    protected  $hidden = ['id_unit_pembangkit'];
    protected  $appends = ['unit_pembangkit_id'];
    
    public $fillable = [
        'name',
        'id_unit_pembangkit',
        'status',
        'level',
        'privilages',
        'application',
        'description'
    ];

    /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [
        'name',
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_unit_pembangkit' => 'integer',
        'name' => 'string',
        'status' => 'integer',
        'level' => 'integer',
        'application' => 'string',
        'description' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    public function getUnitPembangkitIdAttribute($key = 'id_unit_pembangkit')
    {   
        if (array_key_exists($key, $this->attributes)){ 
            return $this->getAttribute($key);
        } 
    }

    public function unitPembangkitId()
    {
        return $this->belongsTo(\App\Models\UnitPembangkit::class, 'id_unit_pembangkit');
    }
    
}
