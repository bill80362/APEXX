<?php

namespace App\Controllers\Admin\Discount;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Discount extends BaseController
{
    use ResponseTrait;
    public $ImageDirPath = "/image/discount";
    public function getList()
    {
        //
        $oDiscount = new \App\Models\Discount\Discount();
        $oDiscount->orderBy("DiscountID", "DESC");
        $List = $oDiscount->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //
        $DiscountType = $this->request->getVar("DiscountType");
        $Title = $this->request->getVar("Title");
        $DiscountPercent = $this->request->getVar("DiscountPercent");
        $GiveName = $this->request->getVar("GiveName");
        $Image1 = $this->request->getVar("Image1");
        $Combine = $this->request->getVar("Combine");
        $Threshold = $this->request->getVar("Threshold");
        $LimitMember = $this->request->getVar("LimitMember");
        $StartTime = $this->request->getVar("StartTime");
        $EndTime = $this->request->getVar("EndTime");
        $Status = $this->request->getVar("Status");
        $MenuID = $this->request->getVar("MenuID");
        //
        $oDiscount = new \App\Models\Discount\Discount();
        $oDiscount->protect(false);
        $DiscountID = $oDiscount->insert([
            "DiscountType"=>$DiscountType,
            "Title"=>$Title,
            "DiscountPercent"=>$DiscountPercent,
            "GiveName"=>$GiveName,
            "Image1"=>$Image1,
            "Combine"=>$Combine,
            "Threshold"=>$Threshold,
            "LimitMember"=>$LimitMember,
            "StartTime"=>$StartTime,
            "EndTime"=>$EndTime,
            "Status"=>$Status,
            "MenuID"=>$MenuID,
        ]);
        if ($oDiscount->errors()) {
            $ErrorMsg = implode(",", $oDiscount->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oDiscount->find($DiscountID);
        return $this->respond(ResponseData::success($Data));
    }
    public function update()
    {
        //
        $ID = $this->request->getVar("ID");
        $DiscountType = $this->request->getVar("DiscountType");
        $Title = $this->request->getVar("Title");
        $DiscountPercent = $this->request->getVar("DiscountPercent");
        $GiveName = $this->request->getVar("GiveName");
        $Image1 = $this->request->getVar("Image1");
        $Combine = $this->request->getVar("Combine");
        $Threshold = $this->request->getVar("Threshold");
        $LimitMember = $this->request->getVar("LimitMember");
        $StartTime = $this->request->getVar("StartTime");
        $EndTime = $this->request->getVar("EndTime");
        $Status = $this->request->getVar("Status");
        $MenuID = $this->request->getVar("MenuID");
        //
        $oDiscount = new \App\Models\Discount\Discount();
        //??????ID
        $Data = $oDiscount->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("?????????????????????"));
        }
        //????????????
        $oDiscount->protect(false);
        $updateData = [
            "DiscountType"=>$DiscountType,
            "Title"=>$Title,
            "DiscountPercent"=>$DiscountPercent,
            "GiveName"=>$GiveName,
            "Combine"=>$Combine,
            "Threshold"=>$Threshold,
            "LimitMember"=>$LimitMember,
            "StartTime"=>$StartTime,
            "EndTime"=>$EndTime,
            "Status"=>$Status,
            "MenuID"=>$MenuID,
        ];
        if ($Image1!==null) {
            //??????????????????
            if (isset($Data["Image1"]) && $Data["Image1"]!="") {
                $FileHostPath = ROOTPATH."public".$Data["Image1"];
                if (file_exists($FileHostPath)) {
                    unlink($FileHostPath);
                }
            }
            //????????????
            $updateData["Image1"] = $Image1;
        }
        $oDiscount->update($ID, $updateData);
        if ($oDiscount->errors()) {
            $ErrorMsg = implode(",", $oDiscount->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oDiscount->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //
        $oDiscount = new \App\Models\Discount\Discount();
        //??????ID
        $Data = $oDiscount->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("?????????????????????"));
        }
        //??????????????????
        if (isset($Data["Image1"]) && $Data["Image1"]!="") {
            $FileHostPath = ROOTPATH."public".$Data["Image1"];
            if (file_exists($FileHostPath)) {
                unlink($FileHostPath);
            }
        }
        //??????DB
        $oDiscount->protect(false);
        $oDiscount->delete($ID);
        if ($oDiscount->errors()) {
            $ErrorMsg = implode(",", $oDiscount->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
    public function uploadImage($ID)
    {
        $oDiscount = new \App\Models\Discount\Discount();
        //??????ID
        $Data = $oDiscount->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("?????????????????????"));
        }
        //???????????? Image ??????
        for ($i=1;$i<=1;$i++) {
            $file = $this->request->getFile('Image'.$i);
            if ($file && $file->isFile()) {
                if ($file->getSizeByUnit('mb')>5) {
                    return $this->respond(ResponseData::fail("??????????????????5MB"));
                }
                if (!in_array($file->getMimeType(), ["image/jpg","image/png","image/gif","image/jpeg","image/webp"], true)) {
                    return $this->respond(ResponseData::fail("??????????????????jpg,png,gif,jpeg,webp"));
                }                //??????????????????
                if (isset($Data["Image".$i]) && $Data["Image".$i]!="") {
                    $FileHostPath = ROOTPATH."public".$Data["Image".$i];
                    if (file_exists($FileHostPath)) {
                        unlink($FileHostPath);
                    }
                }
                //??????????????????
                $name = $file->getRandomName();
                //????????????
                $file->move(ROOTPATH."/public".$this->ImageDirPath, $name);
                //??????DB
                $oDiscount->resetQuery();
                $oDiscount->protect(false);
                $oDiscount->update($ID, ["Image".$i=>$this->ImageDirPath."/".$name]);
            }
        }
        //Res
        $Data = $oDiscount->find($ID);
        return $this->respond(ResponseData::success($Data));
    }
}
