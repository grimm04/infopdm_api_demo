<?php

namespace App\Models;

use Eloquent as Model;
/**
 * @SWG\Definition(
 *      definition="Tribologi",
 *      required={"equipments_id", "unit_item_id", "bulan", "tahun"}, 
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
 *      @SWG\Property(
 *          property="data_detail",
 *          description="data_detail",
 *          type="string"
 *      ), 
 *      @SWG\Property(
 *          property="status",
 *          description="status",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="bulan",
 *          description="bulan",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="tahun",
 *          description="tahun",
 *          type="string"
 *      ),
 *       @SWG\Property(
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

 
class Tribologi extends Model
{

    public $connection = 'mysql-app'; /** Connection 2 */

    public $table = 'tribologi';
    



    public $fillable = [
        'id' ,
        'equipments_id',
        'unit_item_id',
        'data_detail', 
        'status',
        'bulan',
        'tahun',
        'bln_tahun'
    ];

        /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [ 
        'bulan',
        'tahun',
        'status'
    ];


    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'equipments_id' => 'integer',
        'unit_item_id' => 'integer',
        'data_detail' => 'string',
        'status' => 'string',
        'bulan' => 'string',
        'tahun' => 'string',
        'bln_tahun' => 'date:Y-m-d'
    ];

      /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'equipments_id' => 'required|integer',
        'unit_item_id' => 'required|integer',
        'bulan' => 'required|string',
        'tahun' => 'required|integer'
    ];

    public static $rules2 = [
        'equipments_id' => 'integer',
        'unit_item_id' => 'integer',
        'bulan' => 'string',
        'tahun' => 'integer'
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
