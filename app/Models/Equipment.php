<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="Equipment",
 *      required={""}, 
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
 *          property="kks",
 *          description="kks",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="daya",
 *          description="daya",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="rpm",
 *          description="rpm",
 *           type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="note",
 *          description="note",
 *          type="string",
 *          example=null
 *      )
 * )
 */
class Equipment extends Model
{

    public $connection = 'mysql-app'; /** Connection 2 */ 
    public $table = 'equipments';
    public $primaryKey = 'id'; 
    



    public $fillable = [
        'unit_pembangkit_id',
        'name',
        'kks',
        'daya',
        'rpm', 
        'note' 
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'unit_pembangkit_id' => 'integer',
        'name' => 'string',
        'kks' => 'string',
        'daya' => 'integer',
        'rpm' => 'integer',
        'note' => 'string'
    ];


     /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [
        'name', 
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'unit_pembangkit_id' => 'required|integer', 
        'name' => 'string|max:200'
    ];

    public function unitPembangkitId()
    {
        return $this->belongsTo(\App\Models\UnitPembangkit::class, 'unit_pembangkit_id', 'id');
    }
}
