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
use Lcobucci\JWT\Parser;


//models
use App\Models\Vibrasi;
use App\Models\UnitItem;
use App\Models\Termografi;
use App\Models\Tribologi;
use App\Models\UnitPembangkit;

class Helper extends Model
{
    /**
     * HELPER FOR AUTH
     * @params tipe = token/client/client_id
     * - client is detail row by client_id
     */
    public static function findByRequest(?Request $request = null, $tipe='token') 
    {
        $bearerToken = $request !== null ? $request->bearerToken() : RequestFacade::bearerToken();

        $parsedJwt = (new Parser())->parse($bearerToken);

        if ($parsedJwt->hasHeader('jti')) {
            $tokenId = $parsedJwt->getHeader('jti');
        } elseif ($parsedJwt->hasClaim('jti')) {
            $tokenId = $parsedJwt->getClaim('jti');
        } else {
            return null;
        }

        if($tipe=='token'){
            return $tokenId;
        }else if($tipe=='client_id'){
            return Token::find($tokenId)->client->id;
        }else if($tipe=='client'){
            return Client::findOrFail($clientId);
        }

    }


    /**
     * OTHERE HELPER
     */
    public static function crypt($string, $mode = 'encrypt')
    {
        if ($mode == 'encrypt') {
            $result = Crypt::encryptString($string);
        } else if ($mode == 'decrypt') {
            $result = Crypt::decryptString($string);
        }

        return $result;
    }

    public static function uploadImage($inputFile, $uploadDir, $type='avatar'){
        /** Jika folder user tidak ada */
        if(\File::exists("$uploadDir/thumbnail")==false){
            \File::makeDirectory("$uploadDir/thumbnail", 0775, true);
        }

        $originalImage= $inputFile;
        $filename = Carbon::now()->format('Ymdhis')."-".$originalImage->getClientOriginalName();
        $image = Image::make($originalImage);
        $thumbnailPath = "$uploadDir/thumbnail/";
        $originalPath = $uploadDir;

        switch ($type) {
            case 'avatar':
            case 'square':
                $image->fit(300);
                $image->save("$originalPath/$filename");
                $image->resize(60,60);
                $image->save($thumbnailPath.$filename); 
                break;

            case 'free':
            case 'free-medium':
                $w = $image->width();
                $h = $image->height();
                $max = 1900;
                if($type=='free-medium') { $max=1000; }

                if($w > $h) {
                    $image->resize($max, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $image->save("$originalPath/$filename");
                    $image->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($thumbnailPath.$filename); 
                } else {
                    $image->resize(null, $max, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }); 

                    $image->save("$originalPath/$filename");
                    $image->resize(null, 200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($thumbnailPath.$filename); 
                } 
                break;

            case 'news':
                $w = $image->width();
                $h = $image->height();
                if($w==$h){
                    $image->fit(700, 395);
                    $image->save("$originalPath/$filename");

                    $image->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($thumbnailPath.$filename); 
                }
                elseif($w > $h) {
                    $image->fit(700, 395, function ($constraint) {
                        $constraint->upsize();
                    });
                    $image->save("$originalPath/$filename");

                    $image->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($thumbnailPath.$filename); 
                } else {
                    $image->fit(null, 700, function ($constraint) {
                        $constraint->upsize();
                    }); 
                    $image->save("$originalPath/$filename");

                    $image->fit(200, 113, function ($constraint) {
                        $constraint->upsize();
                    });
                    $image->save($thumbnailPath.$filename); 
                } 
                break;
            
            default:
                $filename = false;
                break;
        }

        return $filename;
    }

    
    public static function hashMd5Short($data, $start = 5, $end = 10)
    {
        $result = \substr(md5($data),$start,$end);
        return $result;
    }

    

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function encode_config($arr){
        $config = [];
        $data = json_decode($arr);  
        foreach($data as $key => $value){  
            $temp = [
                'key'=>  $value->no,
                'value'=> strtolower($value->key)
            ]; 
            array_push($config,$temp); 
        } 

        return $config;
    }

    public static function vibrasiUnit($unit, $bulan, $tahun, $config,$zona) 
    {
        $dataset = Vibrasi::where([
            ['unit_item_id','=',$unit],
            ['bulan','=',$bulan],
            ['tahun','=',$tahun],
            ['zone','=',$zona],
        ])->get();
    
        $datazona = [];

        foreach ($dataset as $data) {  
                $data_detail = json_decode($data->data_detail); 
                $detail = [];
                foreach ($data_detail as $det) {
                    $detail[] = [
                        'key' => $det->no,
                        'value'=>$det->value
                    ] ;
                } 
                $datamax = collect($detail); 
                $max = $datamax->where('value', $datamax->max('value'))->first();
                
                $point = null;
                foreach ($config as $conf) {   
                    if($max['key'] == $conf->no && $max['value']!= null) {
                    $point = $conf->name;
                    }
                }  
                $data->point = $point; 
                if($max['value'] != null){ 
                    $datazona[] = [
                        'id'=> $data->id,
                        'equipment_name'=> $data->equipmentId->name,
                        'peak'=> $max['value'],
                        'point'=> $point,
                        'analisis'=> $data->analisis, 
                        'rekomendasi'=>$data->rekomendasi,
                        'attachment'=>$data->attachment
                    ];
                }
        } 
        if (isset($datazona)){ 
            return $datazona;
        }
    }


    
    public static function termografiUnit($unit, $bulan, $tahun,$config) 
    {
        $dataset = Termografi::where([
            ['unit_item_id','=',$unit],
            ['bulan','=',$bulan],
            ['tahun','=',$tahun],
            ['status','=','abnormal'],
        ])->get();
        $datavibrasi = [];
        // return $dataset;
        foreach ($dataset as $data) {  
            $data_detail = json_decode($data->data_detail); 
            $detail = [];
            foreach ($data_detail as $det) {
                $detail[] = [
                    'key' => $det->no,
                    'value'=>$det->value
                ] ;
            } 
            $datamax = collect($detail); 
            $max = $datamax->where('value', $datamax->max('value'))->first();
            
            $point = null;
            foreach ($config as $conf) {   
                if($max['key'] == $conf->no && $max['value']!= null) {
                $point = $conf->name;
                }
            }  
            $data->point = $point; 
            if($max['value'] != null){ 
                $datavibrasi[] = [
                    'id'=> $data->id,
                    'equipment_name'=> $data->equipmentId->name, 
                    'peak'=> $max['value'],
                    'point'=> $point,
                    'status'=> $data->status, 
                    'analisis'=> $data->analisis,
                    'rekomendasi'=>$data->rekomendasi,
                    'attachment'=>$data->attachment
                ];
            }
        } 

         
        if (isset($datavibrasi)){ 
            return $datavibrasi;
        }
    }  

    public static function  tribologiUnit($unit, $bulan, $tahun) {
        $dataset = Tribologi::where([
            ['unit_item_id', $unit],
            ['bulan', $bulan],
            ['tahun', $tahun]
        ])->get();

        $datatribologi = [];
        // return $dataset;
        foreach ($dataset as $data) {   
            $datatribologi[] = [
                'id'=> $data->id, 
                'equipment_name'=> $data->equipmentId->name,  
                'data_detail'=> $data->data_detail, 
                'status'=> $data->status
            ]; 
        } 
    
        if (isset($datatribologi)){ 
            return $datatribologi;
        }
    }
     
    public static function unitPembangkit($id){
         $unit_pembangkit = UnitPembangkit::find($id);
         return $unit_pembangkit;
    }

    public static function unitItem($id){ 
        $unit_item = UnitItem::find($id);
        return $unit_item;
    }

    public static function month($month){
        switch (strtolower($month)) {
            case 'januari':
                return $month = '01';
            case 'februari':
                return $month = '02'; 
            case 'maret':
                return $month = '03';
            case 'april':
                return $month = '04';
            case 'mei':
                return $month = '05';
            case 'juni':
                return $month = '06';
            case 'juli':
                return $month = '07';
            case 'agustus':
                return $month = '08';
            case 'september':
                return $month = '09';
            case 'oktober':
                return $month = '10';
            case 'November':
                return $month = '11';
            case 'desember':
                return $month = '12'; 
                break;
            default:
                return $month = '01';
        }
    }

    public static function isImage($path){
        $allowedMimeTypes = ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'svg', 'svgz', 'cgm', 'djv', 'djvu', 'ico', 'ief','jpe', 'pbm', 'pgm', 'pnm', 'ppm', 'ras', 'rgb', 'tif', 'tiff', 'wbmp', 'xbm', 'xpm', 'xwd']; 
        $explodeImage = explode('.', $path);
        $extension = end($explodeImage);
        $image = false;
        if(in_array($extension, $allowedMimeTypes) ){
            $image = true;
        }   else {
            $image = false;
        }

        return $image;
    }
}
