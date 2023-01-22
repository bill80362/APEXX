<?php

namespace App\Controllers\Member;

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
        $oMember = new \App\Models\Member\Member();
        $oMember->where("Account",$Account);
        $oMember->where("Password",$Password);
        $MemberData = $oMember->first();
        //驗證帳密
        if($MemberData){
            //登入成功
            helper('text');
            $newToken = random_string('md5');
            //作廢可以使用的Token
            $oMemberToken = new \App\Models\Member\MemberToken();
            $oMemberToken->protect(false);
            $oMemberToken->where("MemberID",$MemberData["MemberID"]);
            $oMemberToken->where("ExpireTime >=",date("Y-m-d H:i:s"));
            $List = $oMemberToken->findAll();
            foreach ($List as $Data){
                $oMemberToken->resetQuery();
                $oMemberToken->update($Data["MemberTokenID"],["ExpireTime"=>date("Y-m-d H:i:s")]);
            }
            //建立新Token
            $oMemberToken->resetQuery();
            $oMemberToken->insert([
                "MemberID"=>$MemberData["MemberID"],
                "Token"=>$newToken,
                "ExpireTime"=> date("Y-m-d H:i:s",strtotime("+1 day"))
            ]);
            $oMemberToken->protect(true);
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
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //作廢可以使用的Token
        $oMemberToken = new \App\Models\Member\MemberToken();
        $oMemberToken->protect(false);
        $oMemberToken->where("MemberID",$LoginMemberID);
        $oMemberToken->where("ExpireTime >=",date("Y-m-d H:i:s"));
        $List = $oMemberToken->findAll();
        foreach ($List as $Data){
            $oMemberToken->resetQuery();
            $oMemberToken->update($Data["MemberTokenID"],["ExpireTime"=>date("Y-m-d H:i:s")]);
        }
        //
        //Res
        return $this->respond(ResponseData::success("登出成功"));
    }
    public function register(){
        //
        $Account = $this->request->getVar("Account");
        $Password = $this->request->getVar("Password");
        $Name = $this->request->getVar("Name");
        $Phone = $this->request->getVar("Phone");
//        $Email = $this->request->getVar("Email");
        $Birthday = $this->request->getVar("Birthday");
        $Sex = $this->request->getVar("Sex");
        //
        $oMember = new \App\Models\Member\Member();
        $oMember->protect(false);
        $ID = $oMember->insert([
            "Account"=>$Account,
            "Password"=>$Password,
            "Name"=>$Name,
            "Phone"=>$Phone,
//            "Email"=>$Email,
            "Birthday"=>$Birthday,
            "Sex"=>$Sex,
            "Status"=>"Y",
        ]);
        if($oMember->errors()){
            $ErrorMsg = implode(",",$oMember->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oMember->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function forgetPassword(){
        $Account = $this->request->getVar("Account");
        //
        $oMember = new \App\Models\Member\Member();
        $oMember->where("Account",$Account);
        $MemberData = $oMember->first();
        if(!$MemberData) return $this->respond(ResponseData::fail("找不到此帳號"));
        //
        helper('text');
        $newPassword = random_string('alnum',6);
        $oMember->protect(false);
        $oMember->update($MemberData["MemberID"],[
            "Password" => $newPassword,
        ]);
        //寄出密碼
//        $oMail = new \App\Libraries\Tools\Mail("測試用");
//        $rs = $oMail->send($MemberData["Account"],"網紅廚房密碼重置通知","您的新密碼是:".$newPassword);
        //
        $ViewData = [
            'Password' => $newPassword,
            'SendDate' => date("Y-m-d"),
        ];
        $HTML_Contact = view('/Mail/ForgetPassword', $ViewData);
        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $rs = $oMail->send($MemberData["Account"],"耀聞水果世界密碼重置通知",$HTML_Contact);//給客戶
        //
        if(!$rs)return $this->respond(ResponseData::fail($oMail->ErrorMsg));
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
