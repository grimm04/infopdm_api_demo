<?php

namespace App\Models;

use Eloquent as Model;



/**
 * @SWG\Definition(
 *      definition="UnitPembangkit",
 *      required={"name", "address", "email", "status"}, 
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="address",
 *          description="address",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="image",
 *          description="image",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="vibrasi_config_detail",
 *          description="vibrasi_config_detail",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="termografi_config_detail",
 *          description="termografi_config_detail",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="tribologi_config_detail",
 *          description="tribologi_config_detail",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="status",
 *          description="status",
 *          type="boolean"
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
/**
 * @SWG\Definition(
 *      definition="UnitPembangkitAll",
 *      required={"name", "address", "email", "status"}, 
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="address",
 *          description="address",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="image",
 *          description="image",
 *          type="string"
 *      )
 * )
 */
class UnitPembangkit extends Model
{

    public $connection = 'mysql-app'; /** Connection 2 */

    public $table = 'unit_pembangkit';
    



    public $fillable = [
        'name',
        'address',
        'email',
        'image',
        'status',
        'vibrasi_config_detail',
        'termografi_config_detail',
        'tribologi_config_detail'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'address' => 'string',
        'email' => 'string',
        'image' => 'string',
        'status' => 'boolean',
        'vibrasi_config_detail' => 'string',
        'termografi_config_detail' => 'string',
        'tribologi_config_detail' => 'string'
    ];


       /**
     * The attributes for searchable like %% by keyword request
     *
     * @var array
     */
    public $searchable = [
        'name',
        'address',
        'email',
        'status'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [ 
    ];

    
}
