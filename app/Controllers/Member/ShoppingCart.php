<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class ShoppingCart extends BaseController
{
    use ResponseTrait;
    public function getList(){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oShoppingCart = new \App\Models\ShoppingCart\ShoppingCart();
        $oShoppingCart->where("MemberID",$LoginMemberID);
        $oShoppingCart->orderBy("ShoppingCartID","DESC");
        $List = $oShoppingCart->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create(){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $GoodsID = $this->request->getVar("GoodsID");
        $ColorID = $this->request->getVar("ColorID");
        $SizeID = $this->request->getVar("SizeID");
        //檢查商品是否能放入購物車
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
        $oGoodsStock->where("GoodsID",$GoodsID);
        $oGoodsStock->where("ColorID",$ColorID);
        $oGoodsStock->where("SizeID",$SizeID);
        $GoodsStockData = $oGoodsStock->first();
        if(!$GoodsStockData) return $this->respond(ResponseData::fail("商品庫存資訊錯誤"));
        if($GoodsStockData["Status"]!="Y") return $this->respond(ResponseData::fail("該商品為關閉狀態"));
        if(!$GoodsStockData["Stock"]) return $this->respond(ResponseData::fail("該商品為已無庫存"));
        //
        $oShoppingCart = new \App\Models\ShoppingCart\ShoppingCart();
        $oShoppingCart->protect(false);
        $ShoppingCartID = $oShoppingCart->insert([
            "MemberID"=>$LoginMemberID,
            "GoodsID"=>$GoodsID,
            "ColorID"=>$ColorID,
            "SizeID"=>$SizeID,
        ]);
        if($oShoppingCart->errors()){
            $ErrorMsg = implode(",",$oShoppingCart->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oShoppingCart->find($ShoppingCartID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID){
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oShoppingCart = new \App\Models\ShoppingCart\ShoppingCart();
        $oShoppingCart->where("MemberID",$LoginMemberID);
        //檢查ID
        $Data = $oShoppingCart->find($ID);
        if(!$Data) return $this->respond(ResponseData::fail("找不到該筆資料"));
        //刪除DB
        $oShoppingCart->protect(false);
        $oShoppingCart->delete($ID);
        if($oShoppingCart->errors()){
            $ErrorMsg = implode(",",$oShoppingCart->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }

}
