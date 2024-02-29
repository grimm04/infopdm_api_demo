<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="Rekomendasi",
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
 *          property="status",
 *          description="status",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="feedback",
 *          description="feedback",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="tanggal_open",
 *          description="tanggal_open",
 *          type="string",
 *          format="date"
 *      ),
 *      @SWG\Property(
 *          property="lampiran_open",
 *          description="lampiran_open",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="tanggal_closed",
 *          description="tanggal_closed",
 *          type="string",
 *          format="date"
 *      ),
 *      @SWG\Property(
 *          property="lampiran_closed",
 *          description="lampiran_closed",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="akurasi",
 *          description="akurasi",
 *          type="string"
 *      )
 * )
 */
class Rekomendasi extends Model
{

    public $connection = 'mysql-app'; /** Connection 2 */

    public $table = 'rekomendasi';  

    public $fillable = [ 
        'id',
        'equipments_id',
        'unit_item_id',
        'analisis',
        'rekomendasi',
        'status',
        'feedback',
        'tanggal_open',
        'lampiran_open',
        'tanggal_closed',
        'lampiran_closed',
        'akurasi',
        'token'
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
        'analisis' => 'string',
        'rekomendasi' => 'string',
        'status' => 'string',
        'feedback' => 'string',
        'tanggal_open' => 'date:Y-m-d',
        'lampiran_open' => 'string',
        'tanggal_closed' => 'date:Y-m-d',
        'lampiran_closed' => 'string',
        'akurasi' => 'string',
        'token' => 'string'
    ];


       /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [ 
        'analisis',
        'rekomendasi',
        'feedback',
        'status',
        'akurasi'
    ];
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [ 
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
