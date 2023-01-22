<?php

namespace App\Libraries;

class Checkout
{
    public $MemberID = 0;
    public function __construct($MemberID){
        $this->MemberID = $MemberID;
    }
    public $ErrorMessage = "";
    //各個階段價格優惠
    public $CheckoutList = [];
    public $Total = 0;//訂單售價總金額
    public $DiscountMenuTotal = 0;//分類折扣後的加總金額
    public $DiscountFullTotal = 0;//全館折扣後的加總金額
    public $AfterCouponTotal = 0;//現金折扣後的金額
    //免運優惠、贈品
    public $ShippingFree = false;//是否免運費
    public $DiscountID_ShippingFree = 0;//免運使用的優惠ID
    public $GiveInfo = [];//贈品資訊
    public $CouponInfo = [];
    //
    public $TotalDeliverWeight = 0;//重量加總
    public $TotalDeliverVolume = 0;//體積加總
    //金流
    public $PaymentInfo = [];
    public $PaymentSubtotalFee = 0;
    //物流
    public $ShippingInfo = [];
    public $ShippingFee = 0;
    public $ShippingFeeOutlying = 0;
    public $ShippingStatusOutlying = "N";
    //最終費用
    public $FinalTotal = 0;
    public $FinalTotalOutlying = 0;

    public function cashier(array $GoodsStockArray,$CouponCode,$PaymentID,$ShippingID)
    {
        //購物車計算
        $oGoodsStock = new \App\Models\Goods\GoodsStock();
        $oGoodsStock->select("GoodsStock.*,Goods.*");
        $oGoodsStock->select("GoodsStock.Status AS GoodsStockStatus");
        $oGoodsStock->select("Goods.Status AS GoodsStatus");
        $oGoodsStock->select("Goods.GoodsTimeStart");
        $oGoodsStock->select("Goods.GoodsTimeEnd");
        $oGoodsStock->select("Color.ColorTitle");
        $oGoodsStock->select("Size.SizeTitle");
        $oGoodsStock->join("Goods", "GoodsStock.GoodsID = Goods.GoodsID");
        $oGoodsStock->join("Color", "Color.ColorID=GoodsStock.ColorID");
        $oGoodsStock->join("Size", "Size.SizeID=GoodsStock.SizeID");
        //商品
        $oGoodsStock->groupStart();
        foreach ($GoodsStockArray as $Data) {
            $oGoodsStock->orGroupStart();
            $oGoodsStock->where("GoodsStock.GoodsID", $Data["GoodsID"]);
            $oGoodsStock->where("GoodsStock.ColorID", $Data["ColorID"]);
            $oGoodsStock->where("GoodsStock.SizeID", $Data["SizeID"]);
            $oGoodsStock->groupEnd();
        }
        $oGoodsStock->groupEnd();
        //根據商品資訊，到ＤＢ抓資料，如果購買２個以上的商品，會只有一筆
        $List = $oGoodsStock->findAll();
        //統計每項商品的購買數量
        $moreThanTwo = [];
        foreach ($GoodsStockArray as $key => $Data){
            foreach ($List as $key2=>$Data2){//&& $key!=$key2
                if( $Data["GoodsID"]==$Data2["GoodsID"] && $Data["ColorID"]==$Data2["ColorID"] && $Data["SizeID"]==$Data2["SizeID"]  ){
                    if( isset($moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]]) ){
                        $moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]]++;
                    }
                    else
                        $moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]] = 1;
                }
            }
        }
        //整理重複商品
        $moreThanTwoList = [];
        foreach ($List as $key => $Data) {
            if( isset($moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]]) ){
                $count = $moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]];
                //檢查 重複的數量 <= 庫存
                if($count > $Data["Stock"]){
                    $this->ErrorMessage = $Data["Title"] ."_". $Data["ColorTitle"] ."_". $Data["SizeTitle"]  . " 庫存數量不足:".$Data["Stock"];
                    return false;
                }
                //購買２個以上的商品，逐次放入$moreThanTwoList
                for ($i=1;$i<$count;$i++){
                    $moreThanTwoList[] = $Data;
                }
            }
        }
        $List = array_merge($moreThanTwoList,$List);//$List會沒有購買２次以上的商品，$moreThanTwoList是兩次以上的商品整理表
        if(count($GoodsStockArray)!=count($List)){
            foreach ($GoodsStockArray as $Data){
                //購買商品資訊有誤 查看什麼商品
                $oGoodsStock = new \App\Models\Goods\GoodsStock();
                $oGoodsStock->select("GoodsStock.*,Goods.*");
                $oGoodsStock->select("GoodsStock.Status AS GoodsStockStatus");
                $oGoodsStock->select("Goods.Status AS GoodsStatus");
                $oGoodsStock->select("Color.ColorTitle");
                $oGoodsStock->select("Size.SizeTitle");
                $oGoodsStock->join("Goods", "GoodsStock.GoodsID = Goods.GoodsID");
                $oGoodsStock->join("Color", "Color.ColorID=GoodsStock.ColorID");
                $oGoodsStock->join("Size", "Size.SizeID=GoodsStock.SizeID");
                $oGoodsStock->where("GoodsStock.GoodsID", $Data["GoodsID"]);
                $oGoodsStock->where("GoodsStock.ColorID", $Data["ColorID"]);
                $oGoodsStock->where("GoodsStock.SizeID", $Data["SizeID"]);
                $Temp = $oGoodsStock->first();
                //
                if(!$Temp){
                    $this->ErrorMessage = "購買商品資訊有誤(".$Data["GoodsID"].",".$Data["ColorID"].",".$Data["SizeID"].")";
                    return false;
                }

            }
        }
        //初始化
        $this->Total = 0;
        $this->TotalDeliverWeight = 0;
        $this->TotalDeliverVolume = 0;
        foreach ($List as $key => $Data) {
            //檢查商品狀態
            if ($Data["GoodsStockStatus"] != "Y") {
                $this->ErrorMessage = $Data["Title"] . " 庫存為關閉狀態";
                return false;
            }
            if ($Data["GoodsStatus"] != "Y") {
                $this->ErrorMessage = $Data["Title"] . " 商品為關閉狀態";
                return false;
            }
            if (!$Data["Stock"]) {
                $this->ErrorMessage = $Data["Title"] . " 已無庫存";
                return false;
            }
            //檢查商品是否有設定銷售時間，如果有，是否有超出銷售時間要擋下
            if ($Data["GoodsTimeStart"] && $Data["GoodsTimeEnd"]) {
                if( !(strtotime($Data["GoodsTimeStart"])< time() && time() > strtotime($Data["GoodsTimeEnd"])) ){
                    $this->ErrorMessage = $Data["Title"] . " 商品超出銷售時間限制";
                    return false;
                }
            }
            //各項商品的優惠折扣 預設100%
            $List[$key]["DiscountPercentMenu"] = 100;//群組折扣
            $List[$key]["DiscountPercentFull"] = 100;//全館折扣
//            $List[$key]["DiscountID_PercentMenu"] = 0;//群組折扣使用的優惠ID
//            $List[$key]["DiscountID_PercentFull"] = 0;//全館折扣使用的優惠ID
            $List[$key]["DiscountPercentMenuInfo"] = [];//群組折扣使用的優惠ID
            $List[$key]["DiscountPercentFullInfo"] = [];//全館折扣使用的優惠ID

            //統計結帳總金額
            $this->Total += $Data["SellPrice"];
            //統計體積、重量 加總
            $this->TotalDeliverWeight += $Data["DeliverWeight"];
            $this->TotalDeliverVolume += $Data["DeliverVolume"];
        }
        //抓優惠
        $oDiscount = new \App\Models\Discount\Discount();
        $oDiscount->where("Status", "Y");
        $oDiscount->where("StartTime <=", date("Y-m-d H:i:s"));
        $oDiscount->where("EndTime >=", date("Y-m-d H:i:s"));
        //優惠限定會員
        if (!$this->MemberID)
            $oDiscount->where("LimitMember", "N");
        //
        $oDiscount->orderBy("Threshold", "DESC");
        $DiscountList = $oDiscount->findAll();
        $MenuIDKeyValue = [];
        if($DiscountList){
            $MenuIDArray = array_column($DiscountList,"MenuID");
//            $MenuIDArray = \App\Libraries\Tools\DatabaseTools::ListToKV($DiscountList, "MenuID");
            //優惠包含的商品ID
            $oMenu2Goods = new \App\Models\Menu2Goods\Menu2Goods();
            $oMenu2Goods->whereIn("MenuID", $MenuIDArray);
            $Temp = $oMenu2Goods->findAll();
            foreach ($Temp as $Data) {
                if (!isset($MenuIDKeyValue[$Data["MenuID"]])) $MenuIDKeyValue[$Data["MenuID"]] = [];
                $MenuIDKeyValue[$Data["MenuID"]][] = $Data["GoodsID"];
            }
        }
        foreach ($DiscountList as $key => $Data) {
            //初始化 統計金額
            $DiscountList[$key]["CheckoutPrice"] = 0;
            //優惠包含的商品ID
            $DiscountList[$key]["GoodsIDArray"] = $MenuIDKeyValue[$Data["MenuID"]] ?? [];
        }
        /**分類折扣的金額累積**/
        foreach ($DiscountList as $key => $Data) {
            if ($Data["MenuID"]) {
                foreach ($List as $ShopCartData) {
                    if (in_array($ShopCartData["GoodsID"], $Data["GoodsIDArray"])) {
                        $DiscountList[$key]["CheckoutPrice"] = $DiscountList[$key]["CheckoutPrice"]??0;
                        $DiscountList[$key]["CheckoutPrice"] += $ShopCartData["SellPrice"];
                    }
                }
            }
        }
        /**商品優惠分類折扣%**/
        $this->DiscountMenuTotal = 0;
        foreach ($List as $key => $ShopCartData) {
            foreach ($DiscountList as $DiscountData) {
                //該商品是否有在分類折扣中
                if(in_array($List[$key]["GoodsID"],$DiscountData["GoodsIDArray"])){
                    //打折優惠、群組優惠、統計金額有過門檻
                    if ($DiscountData["DiscountType"] == "P" && $DiscountData["MenuID"] && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                        if ($DiscountData["DiscountPercent"] < $List[$key]["DiscountPercentMenu"]){
                            $List[$key]["DiscountPercentMenu"] = $DiscountData["DiscountPercent"];//群組折扣
//                        $List[$key]["DiscountID_PercentMenu"] = $DiscountData["DiscountID"];//使用的優惠ID
                            $List[$key]["DiscountPercentMenuInfo"] = $DiscountData;
                        }
                    }
                }
            }
            //商品優惠分類折扣後的金額加總
            $this->DiscountMenuTotal += round($List[$key]["SellPrice"]*$List[$key]["DiscountPercentMenu"]/100);
        }
        /**全館優惠金額累積 使用分類折扣後的金額加總**/
        foreach ($DiscountList as $key => $Data) {
            //優惠有指定商品分類，統計該分類金額
            foreach ($List as $ShopCartData) {
                if (in_array($ShopCartData["GoodsID"], $Data["GoodsIDArray"])) {
                    $DiscountList[$key]["CheckoutPrice"] = $this->DiscountMenuTotal;
                }
            }
        }
        /**全館打折**/
        $this->DiscountFullTotal = 0;
        foreach ($List as $key => $ShopCartData) {
            foreach ($DiscountList as $DiscountData) {
                //打折優惠、全館優惠、統計金額有過門檻
                if ($DiscountData["DiscountType"] == "P" && !$DiscountData["MenuID"] && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                    if ($DiscountData["DiscountPercent"] < $List[$key]["DiscountPercentFull"]){
                        $List[$key]["DiscountPercentFull"] = $DiscountData["DiscountPercent"];//折扣
//                        $List[$key]["DiscountID_PercentFull"] = $DiscountData["DiscountID"];//使用的優惠ID
                        $List[$key]["DiscountPercentFullInfo"] = $DiscountData;//使用的優惠ID
                    }

                }
            }
            //打折優惠結算金額 售價x分類打折x全館打折 (打折會堆疊)
            $List[$key]["DiscountPrice"] = round($ShopCartData["SellPrice"] * $ShopCartData["DiscountPercentMenu"] / 100 * $ShopCartData["DiscountPercentFull"] / 100);
            $this->DiscountFullTotal += $List[$key]["DiscountPrice"];
        }
        /**現金折抵優惠券**/
        $this->CouponInfo = [];
        if($CouponCode){
            $oCoupon = new \App\Models\Coupon\Coupon();
            $oCoupon->select("Coupon.*");
            $oCoupon->where("Status", "Y");
            $oCoupon->where("StartTime <=", date("Y-m-d H:i:s"));
            $oCoupon->where("EndTime >=", date("Y-m-d H:i:s"));
            $oCoupon->where("CouponNumber", $CouponCode);
            $oCoupon->where("Threshold <=", $this->DiscountFullTotal);
            $oCoupon->where("CouponCount >", "0");
            //沒有會員ID 則只抓沒限定會員
            if (!$this->MemberID){
                $oCoupon->where("LimitMember", "N");
            } else {
                //有會員ID
                $oCoupon->join("Coupon2Member","Coupon2Member.CouponID=Coupon.CouponID","left");
                $oCoupon->groupStart();
                $oCoupon->groupStart();
                $oCoupon->where("LimitMember", "Y");
                $oCoupon->where("MemberID",$this->MemberID);
                $oCoupon->groupEnd();
                $oCoupon->orWhere("OnlyMember", "N");
                $oCoupon->groupEnd();
            }
            $oCoupon->groupBy("Coupon.CouponID");
            $CouponInfo = $oCoupon->first();
            if(!$CouponInfo){
                $this->ErrorMessage = "現金折抵優惠券錯誤";
                return false;
            }

            $this->CouponInfo = $CouponInfo??[];
        }
        $CouponMoney = $this->CouponInfo["Money"]??0;
        $this->AfterCouponTotal = $this->DiscountFullTotal - $CouponMoney;
        /**是否有免運**/
        foreach ($DiscountList as $DiscountData) {
            //免運優惠、統計金額有過門檻 使用現金折抵後的金額
            if ($DiscountData["DiscountType"] == "D" &&  $this->AfterCouponTotal >= $DiscountData["Threshold"]) {
                $this->ShippingFree = true;
                $this->DiscountID_ShippingFee = $DiscountData["DiscountID"];
            }
        }
        /**是否有贈品**/
        foreach ($DiscountList as $DiscountData) {
            //贈品、統計金額有過門檻
            if ($DiscountData["DiscountType"] == "G" && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                $this->GiveInfo = $DiscountData;
            }
        }
        /**計算退貨金額 平均分配 現金折抵優惠券 **/
        $CouponMoneyAvg = $this->DiscountFullTotal - $this->AfterCouponTotal;
        foreach ($List as $key => $ShopCartData) {
            $List[$key]["RefundPrice"] = $List[$key]["DiscountPrice"];
        }
        $i=0;
        while ($CouponMoneyAvg>0){
            $List[$i]["RefundPrice"]++;
            $CouponMoneyAvg--;
            $i++;
            if($i==count($List)) $i=0;
        }
        /**金流**/
        $this->PaymentInfo = [];
        $this->PaymentSubtotalFee = 0;//金流額外費用
        if($PaymentID){
            $oPayment = new \App\Models\Payment\Payment();
            $oPayment->where("Status","Y");
            $this->PaymentInfo = $oPayment->find($PaymentID);
            if(!$this->PaymentInfo){
                $this->ErrorMessage = "金流選項錯誤";
                return false;
            }
            $PaymentChargePercent = $this->PaymentInfo["ChargePercent"]??0;
            $ChargeFee = $this->PaymentInfo["ChargeFee"]??0;
            $this->PaymentSubtotalFee = $this->AfterCouponTotal * $PaymentChargePercent / 100 + $ChargeFee;
            //金流方式 有限制不能寄送低溫包裹
            if($this->PaymentInfo["DeliveryFrozen"]=="N"){
                foreach ($List as $key => $Data) {
                    if($Data["DeliveryFrozen"]=="Y"){
                        $this->ErrorMessage = "[".$Data["Title"]."]為低溫包裹無法使用此金流方式";
                        return false;
                    }
                }
            }
        }
        /**物流**/
        $this->ShippingInfo = [];
        if($ShippingID){
            $oShipping = new \App\Models\Shipping\Shipping();
            $oShipping->where("Status","Y");
            $this->ShippingInfo = $oShipping->find($ShippingID);
            if(!$this->ShippingInfo){
                $this->ErrorMessage = "物流選項錯誤";
                return false;
            }
            //檢查商品總體積是否超過
            $DeliverVolumeMax = $this->ShippingInfo["DeliverVolumeMax"]??0;
            if($this->TotalDeliverVolume > $DeliverVolumeMax){
                $this->ErrorMessage = "商品總體積(".$this->TotalDeliverVolume.")超過物流限制(".$DeliverVolumeMax.")";
                return false;
            }
            //檢查商品總重量是否超過
            $DeliverWeightMax = $this->ShippingInfo["DeliverWeightMax"]??0;
            if($this->TotalDeliverWeight > $DeliverWeightMax){
                $this->ErrorMessage = "商品總重量(".$this->TotalDeliverWeight.")超過物流限制(".$DeliverWeightMax.")";
                return false;
            }
            //物流方式 有限制不能寄送低溫包裹
            if($this->ShippingInfo["DeliveryFrozen"]=="N"){
                foreach ($List as $key => $Data) {
                    if($Data["DeliveryFrozen"]=="Y"){
                        $this->ErrorMessage = "[".$Data["Title"]."]為低溫包裹無法使用此金流方式";
                        return false;
                    }
                }
            }
            //物流費用 境內境外
            $this->ShippingFee = $this->ShippingInfo["ShippingFee"]??0;
            $this->ShippingFeeOutlying = $this->ShippingInfo["ShippingFeeOutlying"]??0;
            $this->ShippingStatusOutlying = $this->ShippingInfo["StatusOutlying"]??"N";//海外是否可以使用
        }
        /**金流vs物流 綁再一起的檢查**/
        if( $this->PaymentInfo && $this->ShippingInfo ){
            if( in_array($this->PaymentInfo["PaymentType"],["FAMIC2C","UNIMARTC2C"]) ){
                if($this->ShippingInfo["ShippingType"]!=$this->PaymentInfo["PaymentType"]){
                    $this->ErrorMessage = "貨到付款金物流方式為固定選項";
                    return false;
                }
            }
        }
        /**最後費用計算**/
        if($this->ShippingFree){
            $this->FinalTotal = $this->AfterCouponTotal + $this->PaymentSubtotalFee;
            $this->FinalTotalOutlying = $this->FinalTotal;
        }else{
            $this->FinalTotal = $this->AfterCouponTotal + $this->PaymentSubtotalFee + $this->ShippingFee;
            $this->FinalTotalOutlying = $this->AfterCouponTotal + $this->PaymentSubtotalFee + $this->ShippingFeeOutlying;
        }
        //
        $this->CheckoutList = $List;
        return $List;
    }

}
