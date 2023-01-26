<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class Admin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $bearerToken = $request->getServer("HTTP_AUTHORIZATION");
        $bearerToken = str_replace("Bearer ", "", $bearerToken);
        $oAdminToken = new \App\Models\AdminToken();
        $oAdminToken->where("Token", $bearerToken);
        $oAdminToken->where("ExpireTime >=", date("Y-m-d H:i:s"));
        $LoginResult = $oAdminToken->first();
        if (!$LoginResult) {
            header("Access-Control-Allow-Origin:*");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
            header('Access-Control-Allow-Methods: PUT, PATCH, POST, GET, DELETE, OPTIONS');
            //Token失效
            echo json_encode([
                "code" => 302,
                "msg" => "請重新登入"
            ]);
            exit();
        }
        //登入身份設定
        \Config\Services::getLoginAdmin()->setID($LoginResult["AdminID"]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //查看是否有登入
//        if(!session()->get('Location')){
//            if ($request->isAJAX()){
//                $RespondData = [
//                    "Code" => "301",
//                    "Msg" => "請先登入",
//                ];
//                return $response->setJSON($RespondData);
//            }
//        }
    }
}
