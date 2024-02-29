<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Intervention\Image\Facades\Image as Image;
use Carbon\Carbon;
use Mail;

/** For auth */
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;


class AuthHelper extends Model
{

    /**
     * HELPER FOR AUTH
     * @params tipe = token/client/client_id
     * - client is detail row by client_id
     */

     
    public static function findByRequest(?Request $request = null, $tipe='token') 
    {
        // $client = DB::table('oauth_clients')
        //     ->where('password_client', true)
        //     ->first();
        
        $bearerToken = $request !== null ? $request->bearerToken() : RequestFacade::bearerToken();

        $parsedJwt = (new Parser(new JoseEncoder()))->parse($bearerToken);
        // $parsedJwt = (new Parser(new JoseEncoder()))->parse($bearerToken)->claims() ->all()['jti']; 
        // return $parsedJwt; 

        
        if ($parsedJwt->headers()->get('jti') != "") {
            $tokenId = $parsedJwt->headers()->get('jti');
        } elseif ($parsedJwt->claims()->get('jti') != "") {
            $tokenId = $parsedJwt->claims()->get('jti');
        } else {
            return null;
        }

        // return $tokenId;

        if($tipe=='token'){
            return $tokenId;
        }else if($tipe=='client_id'){
            return Token::find($tokenId)->client->id;
        }else if($tipe=='client'){
            return Client::findOrFail($clientId);
        }

    }

    public static function getFieldLoginBy(){
        $login = request()->input('user');

        if(is_numeric($login)){
            $login = AuthHelper::getPhoneNumberValid($login);
            $field = 'no_hp';
        } else
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }

        $request = request()->merge(['user'=>$login,'login_by' => $field, $field => $login]);
        return $request;
    }

    public static function getPhoneNumberValid($number){
        $phone = $number;
        $phone = \str_replace("+62", "", $phone);
        $phone =  (substr($phone, 0, 1)=="0") ? substr($phone, 1, strlen($phone)) : $phone;
        $phone = "62".$phone;

        return $phone;
    }
}