<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="NonRutinVibrasi",
 *      required={""},
 *      @SWG\Property(
 *          property="equipments_id",
 *          description="equipments_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="unit_item_id",
 *          description="unit_item_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      
 *      @SWG\Property(
 *          property="data_detail",
 *          description="data_detail",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="zone",
 *          description="zone",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="analisis",
 *          description="analisis",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="rekomendasi",
 *          description="rekomendasi",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="keterangan",
 *          description="keterangan",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="date",
 *          description="date",
 *          type="string",
 *          format="date"
 *      ),
 *      @SWG\Property(
 *          property="time",
 *          description="time",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="attachment",
 *          description="attachment",
 *          type="string"
 *      )
 * )
 */
class NonRutinVibrasi extends Model
{

    public $connection = 'mysql-app'; /** Connection 2 */

    public $table = 'non_rutin_vibrasi';
    public $primaryKey = 'id';  
    
    public $fillable = [ 
        'equipments_id',
        'unit_item_id', 
        'data_detail',
        'zone',
        'analisis',
        'rekomendasi',
        'keterangan',
        'date' ,
        'time',
        'attachment'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [  
        'data_detail' => 'string',
        'zone' => 'string',
        'analisis' => 'string',
        'rekomendasi' => 'string',
        'keterangan' => 'string',
        'date' => 'date:Y-m-d',
        'time' => 'datetime',
        'attachment' => 'string',
        'equipments_id' => 'integer',
        'unit_item_id' => 'integer'
    ];

    
     /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [ 
        'zone',
        'time',
        'keterangan',
        'rekomendasi'
    ];


    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [ 
        'equipments_id' => 'required', 
        'unit_item_id' => 'required',
        'date' => 'required',
        'time' => 'required'
    ];

    public function unitItemId()
    {
        return $this->belongsTo(\App\Models\UnitItem::class, 'unit_item_id', 'id');
    }

    public function equipmentId()
    {
        return $this->belongsTo(\App\Models\Equipment::class, 'equipments_id', 'id');
    }
 
}
