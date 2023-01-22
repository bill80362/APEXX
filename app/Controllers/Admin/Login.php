<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Login extends BaseController
{
    use ResponseTrait;
    public function login()
    {
        $Account = $this->request->getVar("Account");
        $Password = $this->request->getVar("Password");
        //
        $oAdmin = new \App\Models\Admin();
        $oAdmin->where("Account",$Account);
        $oAdmin->where("Password",$Password);
        $AdminData = $oAdmin->first();
        //驗證帳密
        if($AdminData){
            //登入成功
            helper('text');
            $newToken = random_string('md5');
            //作廢可以使用的Token
            $oAdminToken = new \App\Models\AdminToken();
            $oAdminToken->protect(false);
            $oAdminToken->where("AdminID",$AdminData["AdminID"]);
            $oAdminToken->where("ExpireTime >=",date("Y-m-d H:i:s"));
            $List = $oAdminToken->findAll();
            foreach ($List as $Data){
                $oAdminToken->resetQuery();
                $oAdminToken->update($Data["AdminTokenID"],["ExpireTime"=>date("Y-m-d H:i:s")]);
            }
            //建立新Token
            $oAdminToken->resetQuery();
            $oAdminToken->insert([
                "AdminID"=>$AdminData["AdminID"],
                "Token"=>$newToken,
                "ExpireTime"=> date("Y-m-d H:i:s",strtotime("+1 day"))
            ]);
            $oAdminToken->protect(true);
            //Res
            $ResData = [
                "Token" => $newToken,
            ];
            return $this->respond(ResponseData::success($ResData));
        }
        //Res
        return $this->respond(ResponseData::fail("登入失敗",301));
    }
    public function logout(){
        //取得登入身份
        $LoginAdminID = \Config\Services::getLoginAdmin()->getID();
        //作廢可以使用的Token
        $oAdminToken = new \App\Models\AdminToken();
        $oAdminToken->protect(false);
        $oAdminToken->where("AdminID",$LoginAdminID);
        $oAdminToken->where("ExpireTime >=",date("Y-m-d H:i:s"));
        $List = $oAdminToken->findAll();
        foreach ($List as $Data){
            $oAdminToken->resetQuery();
            $oAdminToken->update($Data["AdminTokenID"],["ExpireTime"=>date("Y-m-d H:i:s")]);
        }
        //
        //Res
        return $this->respond(ResponseData::success("登出成功"));
    }
}
