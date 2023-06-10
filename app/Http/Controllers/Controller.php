<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function handleResponse($msg, $result, $code)
    {
        $res = [
            'status' => true,
            'message' => $msg,
            'data'    => $result,
        ];
        return response()->json($res, $code);
    }

    public function handleError($error, $errorMsg = [], $code = 404)
    {
        $res = [
            'status' => false,
            'message' => $error,
        ];
        if (!empty($errorMsg)) {
            $res['data'] = $errorMsg;
        }
        return response()->json($res, $code);
    }
}
