<?php

namespace App\Libraries;

class ResponseData
{
    public static function success($DataArray)
    {
        return [
            "code" => 200,
            "data" =>$DataArray,
        ];
    }
    public static function fail($ErrorMsg, $code=500)
    {
        return [
            "code" => $code,
            "msg" =>$ErrorMsg,
        ];
    }
}
