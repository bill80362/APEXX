<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class Member extends BaseController
{
    use ResponseTrait;
    public function getMy()
    {
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oMember = new \App\Models\Member\Member();
        $Data = $oMember->find($LoginMemberID);
        //
        unset($Data["Password"]);
        //Res
        return $this->respond(ResponseData::success($Data));
    }
    public function updateMy()
    {
        $Phone = $this->request->getVar("Phone");
        $Email = $this->request->getVar("Email");
        $Birthday = $this->request->getVar("Birthday");
        $Sex = $this->request->getVar("Sex");
        //
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oMember = new \App\Models\Member\Member();
        $oMember->protect(false);
        $oMember->update($LoginMemberID, [
            "Birthday" => $Birthday,
            "Sex" => $Sex,
//            "Phone" => $Phone,
//            "Email" => $Email,
        ]);
        if ($oMember->errors()) {
            $ErrorMsg = implode(",", $oMember->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updatePassword()
    {
        $Password = $this->request->getVar("Password");
        $NewPassword = $this->request->getVar("NewPassword");
        //
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oMember = new \App\Models\Member\Member();
        $MemberData = $oMember->find($LoginMemberID);
        if ($MemberData["Password"]!=$Password) {
            return $this->respond(ResponseData::fail("原密碼錯誤"));
        }
        //
        $oMember->resetQuery();
        $oMember->protect(false);
        $oMember->update($LoginMemberID, [
            "Password" => $NewPassword,
        ]);
        if ($oMember->errors()) {
            $ErrorMsg = implode(",", $oMember->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
