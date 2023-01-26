<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Favorite extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oFavorite = new \App\Models\Favorite\Favorite();
        $oFavorite->where("MemberID", $LoginMemberID);
        $oFavorite->orderBy("FavoriteID", "DESC");
        $List = $oFavorite->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function create()
    {
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $FavoriteGoodsID = $this->request->getVar("FavoriteGoodsID");
        //檢查商品是否能放入購物車
        $oGoods = new \App\Models\Goods\Goods();
        $GoodsData = $oGoods->find($FavoriteGoodsID);
        if (!$GoodsData) {
            return $this->respond(ResponseData::fail("找不到商品資訊"));
        }
        //
        $oFavorite = new \App\Models\Favorite\Favorite();
        $oFavorite->protect(false);
        $FavoriteID = $oFavorite->insert([
            "MemberID"=>$LoginMemberID,
            "FavoriteGoodsID"=>$FavoriteGoodsID,
        ]);
        if ($oFavorite->errors()) {
            $ErrorMsg = implode(",", $oFavorite->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        $Data = $oFavorite->find($FavoriteID);
        return $this->respond(ResponseData::success($Data));
    }
    public function del($ID)
    {
        //取得登入身份
        $LoginMemberID = \Config\Services::getLoginMember()->getID();
        //
        $oFavorite = new \App\Models\Favorite\Favorite();
        $oFavorite->where("MemberID", $LoginMemberID);
        //檢查ID
        $Data = $oFavorite->find($ID);
        if (!$Data) {
            return $this->respond(ResponseData::fail("找不到該筆資料"));
        }
        //刪除DB
        $oFavorite->protect(false);
        $oFavorite->delete($ID);
        if ($oFavorite->errors()) {
            $ErrorMsg = implode(",", $oFavorite->errors());
            return $this->respond(ResponseData::fail($ErrorMsg));
        }
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
