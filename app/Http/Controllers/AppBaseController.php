<?php

namespace App\Http\Controllers;

use InfyOm\Generator\Utils\ResponseUtil;
use Response;
use Config;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1/infopdm",
 *   @SWG\Info(
 *     title="Infopdm APIs Documentation",
 *     version="1.0.0",
 *   ),
 *   @SWG\SecurityScheme(
 *      securityDefinition="Bearer",
 *      type="apiKey",
 *      name="Authorization",
 *      in="header"
 *     )
 *  )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message, $code=200)
    {
        $message = strpos($message, 'response.') !== false ? Config::get($message) : $message;

        if($code==200){
            return Response::json(ResponseUtil::makeResponse($message, $result));
        }else{
            return $this->sendError($message, $code);
        }
    }

    public function sendError($message, $code = 404, $errors=null)
    {
        $message = strpos($message, 'response.') !== false ? Config::get($message) : $message;

        $errResponse['data'] = null;
        $errResponse = array_merge($errResponse, ResponseUtil::makeError($message)); 

        if($errors){
            $errResponse['errors'] = $errors;
        }
        if(isset($errors['invalid'])){
            $errorsAll = is_object($errors['invalid']) ? $errors['invalid']->all(): $errors['invalid'];
            $errResponse['errors_string'] = implode(' ',$errorsAll);
        }
        else{
            $errResponse['errors_string'] = $message;
        }
        
        return Response::json($errResponse, $code);
    }

    public function sendSuccess($message)
    {
        $message = strpos($message, 'response.') !== false ? Config::get($message) : $message;

        return Response::json([
            'success' => true,
            'data' => null,
            'message' => $message
        ], 200);
    }
}
