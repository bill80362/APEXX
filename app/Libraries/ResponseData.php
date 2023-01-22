<?php

namespace App\Libraries;

class ResponseData
{
    static public function success($DataArray){
        return [
            "code" => 200,
            "data" =>$DataArray,
        ];
    }
    static public function fail($ErrorMsg,$code=500){
        return [
            "code" => $code,
            "msg" =>$ErrorMsg,
        ];
    }
}
