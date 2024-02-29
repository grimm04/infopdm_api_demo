<?php

namespace App\Models\Logs;
use Illuminate\Support\Facades\Auth;

use Eloquent as Model;

/**
 * @SWG\Definition(
 *      definition="LogUserStatus",
 *      required={"id_user"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="id_log_prev",
 *          description="id_log_prev",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="status_user",
 *          description="status_user",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="id_user",
 *          description="id_user",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="tgl_ubah",
 *          description="tgl_ubah",
 *          type="string",
 *          format="date-time"
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
class LogUserStatus extends Model
{

    public $table = 'trans_log_status_user';
    public $primaryKey = 'id_log';
    public $timestamps = false;  


    public $fillable = [
        'id_log_prev',
        'status_user',
        'id_user',
        'tgl_ubah',
        'id_user_admin'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_log_prev' => 'integer',
        'status_user' => 'string',
        'id_user' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id_user' => 'required'
    ];

    public static function lastStatus(){
        $check = LogUserStatus::select('id_log')->orderBy('tgl_ubah', 'desc')->first();
        $result = ($check) ? $check->id_log : null;
        return $result;
    }

    public static function insertLogUserStatus($id_user, $statusUserOld, $statusUserNew){
        if($statusUserOld!=$statusUserNew){
            LogUserStatus::create([
                'id_log_prev'=> LogUserStatus::lastStatus(),
                'id_user' => $id_user, // user yg di ubah
                'status_user' =>$statusUserNew,
                'tgl_ubah' => \Carbon\Carbon::now(),
                'id_user_admin'=> Auth::user()->id_user
            ]);
        }
    }
}
