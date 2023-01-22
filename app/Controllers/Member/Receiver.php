<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Receiver extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oReceiver = new \App\Models\Receiver\Receiver();
        $oReceiver->where("MemberID",$LoginMemberID);
        $oReceiver->orderBy("ReceiverID","DESC");
        $List = $oReceiver->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $Name = $this->request->getVar("Name");
        $Zip = $this->request->getVar("Zip");
        $Address = $this->request->getVar("Address");
        $Phone = $this->request->getVar("Phone");
        $Seq = $this->request->getVar("Seq");
        //
        $oReceiver = new \App\Models\Receiver\Receiver();
        $oReceiver->protect(false);
        $ReceiverID = $oReceiver->insert([
            "MemberID"=>$LoginMemberID,
            "Name"=>$Name,
            "Zip"=>$Zip,
            "Address"=>$Address,
            "Phone"=>$Phone,
            "Seq"=>$Seq,
        ]);
        if($oReceiver->errors()){
            $ErrorMsg = implode(",",$oReceiver->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oReceiver->find($ReceiverID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oReceiver = new \App\Models\Receiver\Receiver();
        $oReceiver->where("MemberID",$LoginMemberID);
        //檢查ID
        $Data = $oReceiver->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //刪除DB
        $oReceiver->protect(false);
        $oReceiver->delete($ID);
        if($oReceiver->errors()){
            $ErrorMsg = implode(",",$oReceiver->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function updateSeqBatch(){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $SeqArray = $this->request->getVar();
        if(!is_array($SeqArray)) return $this->respond(ResponseData::fail("資料須為陣列"));
        //更新排序
        $oReceiver = new \App\Models\Receiver\Receiver();
        $oReceiver->protect(false);
        foreach ($SeqArray as $key=>$Data){
            //
            $Temp = $oReceiver->find($Data->ID);
            if(!$Temp || $Temp["MemberID"]!=$LoginMemberID) continue;
            //
            $oReceiver->update($Data->ID,["Seq"=>$Data->Seq]);
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }

}
