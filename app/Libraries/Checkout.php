<?php

namespace App\Libraries;

class Checkout
{
    public $MemberID = 0;
    public function __construct($MemberID)
    {
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
    public $GiveInfo = [];//贈品資訊(多筆)
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

    public function cashier(array $GoodsStockArray, $CouponCode, $PaymentID, $ShippingID)
    {
        //資料一致化，將有缺的部分補上-1
        foreach ($GoodsStockArray as $key => $Data) {
            $GoodsStockArray[$key]["ColorID"] = $Data["ColorID"]??-1;
            $GoodsStockArray[$key]["SizeID"] = $Data["SizeID"]??-1;
            $GoodsStockArray[$key]["CustomSpecID"] = $Data["CustomSpecID"]??-1;
        }
        //購物車計算
        $db = \Config\Database::connect();
        // 一般商品的庫存
        $sql = "SELECT `GoodsStock`.`GoodsID`, 
                       `GoodsStock`.`ColorID`, 
                       `GoodsStock`.`SizeID`, 
                       `GoodsStock`.`Stock`, 
                       `GoodsStock`.`DeliverVolume`,
                       `GoodsStock`.`DeliverWeight`,
                       `GoodsStock`.`Price`, 
                       `GoodsStock`.`SellPrice`,
                       `GoodsStock`.`MemberSellPrice`,
                       `GoodsStock`.`Status` AS `GoodsStockStatus`,
                       `Goods`.*,
                       `Goods`.`Status` AS `GoodsStatus`,
                       `Color`.`ColorTitle`,
                       `Size`.`SizeTitle`,
                       '' AS `SpecCategoryID`,
                       '' AS `BlacklistSpecID`,
                       '' AS `ChangePriceSpec` 
                FROM `GoodsStock`
                JOIN `Goods` ON `GoodsStock`.`GoodsID` = `Goods`.`GoodsID` AND (IFNULL(`Goods`.`IsCustom`,'') = '' OR `Goods`.`IsCustom` = 'N')
                JOIN `Color` ON `Color`.`ColorID` = `GoodsStock`.`ColorID`
                JOIN `Size` ON `Size`.`SizeID` = `GoodsStock`.`SizeID`";
        $sql .= " WHERE ";
        foreach ($GoodsStockArray as $Data) {
            $sql .= "(";
            $sql .= "`GoodsStock`.`GoodsID` = ".$db->escape($Data["GoodsID"])." AND ";
            $sql .= "`GoodsStock`.`ColorID` = ".$db->escape($Data["ColorID"])." AND ";
            $sql .= "`GoodsStock`.`SizeID` = ".$db->escape($Data["SizeID"]);
            $sql .= ")";
            $sql .= " OR ";
        }
        $sql = substr($sql, 0, strlen($sql) - 3);
        $sql .= " UNION ";
        // 取得客製化商品的庫存
        $sql .= "SELECT `CustomGoodsStock`.`GoodsID`, 
                        -1 AS `ColorID`,  
                        -1 AS `SizeID`, 
                        `CustomGoodsStock`.`Stock`,
                        `CustomGoodsStock`.`DeliverVolume`,
                        `CustomGoodsStock`.`DeliverWeight`,
                        `CustomGoodsStock`.`Price`,
                        `CustomGoodsStock`.`SellPrice`,
                        `CustomGoodsStock`.`MemberSellPrice`,
                        `CustomGoodsStock`.`Status` AS `GoodsStockStatus`,
                        `Goods`.*,
                        `Goods`.`Status` AS `GoodsStatus`,
                        '' AS `ColorTitle`,
                        '' AS `SizeTitle`,
                        /* 規格分類 */
                        IFNULL((SELECT GROUP_CONCAT(CustomGoodsSpecCategory.SpecCategoryID) FROM CustomGoodsSpecCategory WHERE CustomGoodsSpecCategory.GoodsID = Goods.GoodsID ORDER BY CustomGoodsSpecCategory.SpecCategoryID), '') AS `SpecCategoryID`,
                        /* 規格黑名單(無法購買組合) */
                        IFNULL((SELECT GROUP_CONCAT(CustomGoodsSpecBlacklist.CustomSpecID SEPARATOR '|') FROM CustomGoodsSpecBlacklist WHERE CustomGoodsSpecBlacklist.GoodsID = Goods.GoodsID ORDER BY CustomGoodsSpecBlacklist.BlacklistID), '') AS `BlacklistSpecID`,
                        /* 組合異動價 */
                        IFNULL((SELECT GROUP_CONCAT(CONCAT_WS('&', CustomGoodsChangePrice.CustomSpecID, CONVERT(CustomGoodsChangePrice.ChangePrice, CHAR)) SEPARATOR '|') FROM CustomGoodsChangePrice WHERE CustomGoodsChangePrice.GoodsID = Goods.GoodsID ORDER BY CustomGoodsChangePrice.ChangePriceID), '') AS `ChangePriceSpec` 
                FROM `CustomGoodsStock`
                JOIN `Goods` ON `CustomGoodsStock`.`GoodsID` = `Goods`.`GoodsID` AND `Goods`.`IsCustom` = 'Y'";
        $sql .= " WHERE ";
        foreach ($GoodsStockArray as $Data) {
            $sql .= "(";
            $sql .= "`CustomGoodsStock`.`GoodsID` = ".$db->escape($Data["GoodsID"]);
            $sql .= ")";
            $sql .= " OR ";
        }
        $sql = substr($sql, 0, strlen($sql) - 3);
        //根據商品資訊，到ＤＢ抓資料，如果購買２個以上的商品，會只有一筆
        $query = $db->query($sql);
        $List = $query->getResultArray();

        //統計每項商品的購買數量
        $moreThanTwo = [];
        foreach ($GoodsStockArray as $key => $Data) {
            foreach ($List as $key2=>$Data2) {//&& $key!=$key2
                if ($Data["GoodsID"]==$Data2["GoodsID"] && $Data["ColorID"]==$Data2["ColorID"] && $Data["SizeID"]==$Data2["SizeID"]) {
                    if (isset($moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]])) {
                        $moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]]++;
                    } else {
                        $moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]] = 1;
                    }
                }
            }
        }
        //整理重複商品
        $moreThanTwoList = [];
        foreach ($List as $key => $Data) {
            if (isset($moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]])) {
                $count = $moreThanTwo[$Data["GoodsID"]][$Data["ColorID"]][$Data["SizeID"]];
                //檢查 重複的數量 <= 庫存
                if ($count > $Data["Stock"]) {
                    if (isset($Data["IsCustom"]) && $Data["IsCustom"] == "Y") {
                        $this->ErrorMessage = $Data["Title"] . " 庫存數量不足:".$Data["Stock"];
                    } else {
                        $this->ErrorMessage = $Data["Title"] ."_". $Data["ColorTitle"] ."_". $Data["SizeTitle"]  . " 庫存數量不足:".$Data["Stock"];
                    }
                    return false;
                }
                //購買２個以上的商品，逐次放入$moreThanTwoList
                for ($i=1;$i<$count;$i++) {
                    $moreThanTwoList[] = $Data;
                }
            }
        }
        $List = array_merge($moreThanTwoList, $List);//$List會沒有購買２次以上的商品，$moreThanTwoList是兩次以上的商品整理表
        if (count($GoodsStockArray)!=count($List)) {
            foreach ($GoodsStockArray as $Data) {
                //購買商品資訊有誤 查看什麼商品
                $sql = "SELECT `GoodsStock`.`GoodsID` 
                        FROM `GoodsStock`
                        JOIN `Goods` ON `GoodsStock`.`GoodsID` = `Goods`.`GoodsID` AND (IFNULL(`Goods`.`IsCustom`,'') = '' OR `Goods`.`IsCustom` = 'N')
                        JOIN `Color` ON `Color`.`ColorID`=`GoodsStock`.`ColorID`
                        JOIN `Size` ON `Size`.`SizeID`=`GoodsStock`.`SizeID`";
                $sql .= " WHERE ";
                $sql .= "`GoodsStock`.`GoodsID` = ".$db->escape($Data["GoodsID"])." AND ";
                $sql .= "`GoodsStock`.`ColorID` = ".$db->escape($Data["ColorID"])." AND ";
                $sql .= "`GoodsStock`.`SizeID` = ".$db->escape($Data["SizeID"]);
                $sql .= " UNION ";
                $sql .= "SELECT `CustomGoodsStock`.`GoodsID` 
                         FROM `CustomGoodsStock`
                         JOIN `Goods` ON `CustomGoodsStock`.`GoodsID` = `Goods`.`GoodsID` AND `Goods`.`IsCustom` = 'Y'";
                $sql .= " WHERE ";
                $sql .= "`CustomGoodsStock`.`GoodsID` = ".$db->escape($Data["GoodsID"]);
                $query = $db->query($sql);
                $Temp = $query->getResultArray();

                if (!$Temp) {
                    if (isset($Data["CustomSpecID"]) && $Data["CustomSpecID"] != -1) {
                        $this->ErrorMessage = "購買客製化商品資訊有誤(".$Data["GoodsID"].")";
                    } else {
                        $this->ErrorMessage = "購買一般商品資訊有誤(".$Data["GoodsID"].",".$Data["ColorID"].",".$Data["SizeID"].")";
                    }
                    return false;
                }
            }
        }
        foreach ($List as $key => $Data) {
            //將庫存資料集合中補上客製規格資訊，預設空白
            $List[$key]["CustomSpecID"] = "";
            //如果是會員，以[會員售價]做後續計算
            if ($this->MemberID) {
                $List[$key]["SellPrice"] = $Data["MemberSellPrice"]??$$Data["SellPrice"];
            }
        }
        foreach ($GoodsStockArray as $Data) {
            //補上消費者購買的規格資訊
            foreach ($List as $key2 => $Data2) {
                if ($Data["GoodsID"] == $Data2["GoodsID"] && $List[$key2]["CustomSpecID"] == "") {
                    $List[$key2]["CustomSpecID"] = $Data["CustomSpecID"];
                    break;
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
                if (!(strtotime($Data["GoodsTimeStart"])< time() && time() > strtotime($Data["GoodsTimeEnd"]))) {
                    $this->ErrorMessage = $Data["Title"] . " 商品超出銷售時間限制";
                    return false;
                }
            }
            /*
            ======範例=====
            。商品A
            -規格分類：尺寸。規格有：大、中、小
            -規格分類：顏色。規格有：白、黑
            -規格分類：型號。規格有：Ａ、Ｂ、Ｃ

            。結帳每種規格分類至少要選擇一個

            。設定額外處理限制購買：
            商品Ａ＋ 大、白 => 則只要規格同時選中大、白，不管型號是什麼都不能購買
            商品Ａ＋ 大、白、Ａ => 則只要規格同時選中大、白、Ａ，不能購買

            。設定額外處理價格調整：
            商品Ａ＋大(只能單選) +100元 => ＥＸ：大、白、Ａ 就會+100
            */
            //客製化商品處理限制購買 & 規格組合增減售價
            if (isset($Data["IsCustom"]) && $Data["IsCustom"] == "Y") {
                $IsPurchaseArray = [];
                $PurchaseSpecCategoryIDArray = [];
                $CustomSpecIDArray = explode(",", $Data["CustomSpecID"]);
                //消費者目前有購買的規格分類
                if (count($CustomSpecIDArray)>0) {
                    $PurchaseSpecCategoryIDArray = \App\Libraries\CustomGoods::findCustomSpecList($Data["GoodsID"], [], $CustomSpecIDArray);
                }
                //商品本身對應到的規格分類
                if (isset($Data["SpecCategoryID"]) && $Data["SpecCategoryID"] != "") {
                    $SpecCategoryIDArray = explode(",", $Data["SpecCategoryID"]);
                    //將兩邊的規格分類進行比對
                    foreach ($SpecCategoryIDArray as $key2 => $SpecCategoryID) {
                        $IsPurchaseArray[$key2] = "N";
                        foreach ($PurchaseSpecCategoryIDArray as $PurchaseData) {
                            if ($PurchaseData["SpecCategoryID"] == $SpecCategoryID && $IsPurchaseArray[$key2] == "N") {
                                $IsPurchaseArray[$key2] = "Y";
                                break;
                            }
                        }
                    }
                }
                //是否每個規格分類都有購買
                foreach ($IsPurchaseArray as $IsPurchase) {
                    if ($IsPurchase == "N") {
                        $this->ErrorMessage = $Data["Title"] . " 客製商品的每個規格分類皆需要購買";
                        return false;
                    }
                }
                //不可販售的規格組合
                if (isset($Data["BlacklistSpecID"]) && $Data["BlacklistSpecID"] != "") {
                    $BlacklistSpecIDArray = explode("|", $List[$key]["BlacklistSpecID"]);
                    foreach ($BlacklistSpecIDArray as $BlacklistSpecID) {
                        if ($BlacklistSpecID == "") {
                            continue;
                        }
                        //資料比對
                        if (\App\Libraries\Checkout::compareSeparatorString($Data["CustomSpecID"], $BlacklistSpecID) == true) {
                            $this->ErrorMessage = $Data["Title"] . " 客製商品出現不可販售的規格組合";
                            return false;
                        }
                    }
                }
                //根據規格組合增減售價
                if (isset($Data["ChangePriceSpec"]) && $Data["ChangePriceSpec"] != "") {
                    $ChangePriceSpecArray = explode("|", $List[$key]["ChangePriceSpec"]);
                    foreach ($ChangePriceSpecArray as $ChangePriceSpec) {
                        if ($ChangePriceSpec == "") {
                            continue;
                        }
                        $CustomSpecID = explode("&", $ChangePriceSpec)[0]??"";
                        $ChangePrice = explode("&", $ChangePriceSpec)[1]??0;
                        //資料比對
                        if (\App\Libraries\Checkout::compareSeparatorString($Data["CustomSpecID"], $CustomSpecID) == true) {
                            $List[$key]["SellPrice"] = $Data["SellPrice"] + $ChangePrice;
                        }
                    }
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
        if (!$this->MemberID) {
            $oDiscount->where("LimitMember", "N");
        }
        //
        $oDiscount->orderBy("Threshold", "DESC");
        $DiscountList = $oDiscount->findAll();
        $MenuIDKeyValue = [];
        if ($DiscountList) {
            $MenuIDArray = array_column($DiscountList, "MenuID");
//            $MenuIDArray = \App\Libraries\Tools\DatabaseTools::ListToKV($DiscountList, "MenuID");
            //優惠包含的商品ID
            $oMenu2Goods = new \App\Models\Menu2Goods\Menu2Goods();
            $oMenu2Goods->whereIn("MenuID", $MenuIDArray);
            $Temp = $oMenu2Goods->findAll();
            foreach ($Temp as $Data) {
                if (!isset($MenuIDKeyValue[$Data["MenuID"]])) {
                    $MenuIDKeyValue[$Data["MenuID"]] = [];
                }
                $MenuIDKeyValue[$Data["MenuID"]][] = $Data["GoodsID"];
            }
        }
        foreach ($DiscountList as $key => $Data) {
            //初始化 統計金額
            $DiscountList[$key]["CheckoutPrice"] = 0;
            //優惠包含的商品ID
            $DiscountList[$key]["GoodsIDArray"] = $MenuIDKeyValue[$Data["MenuID"]] ?? [];
        }
        /**分類[折扣]的金額累積**/
        foreach ($DiscountList as $key => $Data) {
            if ($Data["DiscountType"] == "P" && $Data["MenuID"]) {
                foreach ($List as $ShopCartData) {
                    if (in_array($ShopCartData["GoodsID"], $Data["GoodsIDArray"], true)) {
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
                if (in_array($List[$key]["GoodsID"], $DiscountData["GoodsIDArray"], true)) {
                    //打折優惠、群組優惠、統計金額有過門檻
                    if ($DiscountData["DiscountType"] == "P" && $DiscountData["MenuID"] && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                        if ($DiscountData["DiscountPercent"] < $List[$key]["DiscountPercentMenu"]) {
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
        /**分類滿額免運**/
        foreach ($DiscountList as $key => $Data) {
            if ($Data["DiscountType"] == "D" && $Data["MenuID"]) {
                foreach ($List as $ShopCartData) {
                    if (in_array($ShopCartData["GoodsID"], $Data["GoodsIDArray"], true)) {
                        $DiscountList[$key]["CheckoutPrice"] = $DiscountList[$key]["CheckoutPrice"]??0;
                        $DiscountList[$key]["CheckoutPrice"] += round($List[$key]["SellPrice"]*$List[$key]["DiscountPercentMenu"]/100);//商品優惠分類折扣後的金額
                    }
                }
            }
        }
        foreach ($DiscountList as $DiscountData) {
            if ($DiscountData["DiscountType"] == "D" && !$DiscountData["MenuID"] && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                $this->ShippingFree = true;
                $this->DiscountID_ShippingFree = $DiscountData["DiscountID"];
            }
        }
        /**分類滿額送贈品**/
        //金額累計-使用商品優惠分類折扣後的金額
        foreach ($DiscountList as $key => $Data) {
            if ($Data["DiscountType"] == "G" && $Data["MenuID"]) {
                foreach ($List as $ShopCartData) {
                    if (in_array($ShopCartData["GoodsID"], $Data["GoodsIDArray"], true)) {
                        $DiscountList[$key]["CheckoutPrice"] = $DiscountList[$key]["CheckoutPrice"]??0;
                        $DiscountList[$key]["CheckoutPrice"] += round($List[$key]["SellPrice"]*$List[$key]["DiscountPercentMenu"]/100);//商品優惠分類折扣後的金額
                    }
                }
            }
        }
        //查看有過門檻的贈品
        foreach ($DiscountList as $DiscountData) {
            if ($DiscountData["DiscountType"] == "G" && !$DiscountData["MenuID"] && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                $this->GiveInfo[] = $DiscountData;
            }
        }
        /**全館優惠金額累積 使用分類折扣後的金額加總**/
        foreach ($DiscountList as $key => $Data) {
            //優惠有指定商品分類，統計該分類金額
            foreach ($List as $ShopCartData) {
//                if (in_array($ShopCartData["GoodsID"], $Data["GoodsIDArray"], true)) {
                $DiscountList[$key]["CheckoutPrice"] = $this->DiscountMenuTotal;
//                }
            }
        }
        /**全館打折**/
        $this->DiscountFullTotal = 0;
        foreach ($List as $key => $ShopCartData) {
            foreach ($DiscountList as $DiscountData) {
                //打折優惠、全館優惠、統計金額有過門檻
                if ($DiscountData["DiscountType"] == "P" && !$DiscountData["MenuID"] && $DiscountData["CheckoutPrice"] >= $DiscountData["Threshold"]) {
                    if ($DiscountData["DiscountPercent"] < $List[$key]["DiscountPercentFull"]) {
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
        if ($CouponCode) {
            $oCoupon = new \App\Models\Coupon\Coupon();
            $oCoupon->select("Coupon.*");
            $oCoupon->where("Status", "Y");
            $oCoupon->where("StartTime <=", date("Y-m-d H:i:s"));
            $oCoupon->where("EndTime >=", date("Y-m-d H:i:s"));
            $oCoupon->where("CouponNumber", $CouponCode);
            $oCoupon->where("Threshold <=", $this->DiscountFullTotal);
            $oCoupon->where("CouponCount >", "0");
            //沒有會員ID 則只抓沒限定會員
            if (!$this->MemberID) {
                $oCoupon->where("LimitMember", "N");
            } else {
                //有會員ID
                $oCoupon->join("Coupon2Member", "Coupon2Member.CouponID=Coupon.CouponID", "left");
                $oCoupon->groupStart();
                $oCoupon->groupStart();
                $oCoupon->where("LimitMember", "Y");
                $oCoupon->where("MemberID", $this->MemberID);
                $oCoupon->groupEnd();
                $oCoupon->orWhere("OnlyMember", "N");
                $oCoupon->groupEnd();
            }
            $oCoupon->groupBy("Coupon.CouponID");
            $CouponInfo = $oCoupon->first();
            if (!$CouponInfo) {
                $this->ErrorMessage = "現金折抵優惠券錯誤";
                return false;
            }

            $this->CouponInfo = $CouponInfo??[];
        }
        $CouponMoney = $this->CouponInfo["Money"]??0;
        $this->AfterCouponTotal = $this->DiscountFullTotal - $CouponMoney;
        /**全館免運**/
        if (!$this->ShippingFree) {
            //已經免運，就不用再計算
            foreach ($DiscountList as $DiscountData) {
                //免運優惠、統計金額有過門檻 使用現金折抵後的金額
                if ($DiscountData["DiscountType"] == "D" && !$DiscountData["MenuID"] &&  $this->AfterCouponTotal >= $DiscountData["Threshold"]) {
                    $this->ShippingFree = true;
                    $this->DiscountID_ShippingFree = $DiscountData["DiscountID"];
                }
            }
        }
        /**全館送贈品**/
        foreach ($DiscountList as $DiscountData) {
            //贈品、統計金額有過門檻
            if ($DiscountData["DiscountType"] == "G" && !$DiscountData["MenuID"] && $this->AfterCouponTotal >= $DiscountData["Threshold"]) {
                $this->GiveInfo[] = $DiscountData;
            }
        }
        /**計算退貨金額 平均分配 現金折抵優惠券 **/
        $CouponMoneyAvg = $this->DiscountFullTotal - $this->AfterCouponTotal;
        foreach ($List as $key => $ShopCartData) {
            $List[$key]["RefundPrice"] = $List[$key]["DiscountPrice"];
        }
        $i=0;
        while ($CouponMoneyAvg>0) {
            $List[$i]["RefundPrice"]++;
            $CouponMoneyAvg--;
            $i++;
            if ($i==count($List)) {
                $i=0;
            }
        }
        /**金流**/
        $this->PaymentInfo = [];
        $this->PaymentSubtotalFee = 0;//金流額外費用
        if ($PaymentID) {
            $oPayment = new \App\Models\Payment\Payment();
            $oPayment->where("Status", "Y");
            $this->PaymentInfo = $oPayment->find($PaymentID);
            if (!$this->PaymentInfo) {
                $this->ErrorMessage = "金流選項錯誤";
                return false;
            }
            $PaymentChargePercent = $this->PaymentInfo["ChargePercent"]??0;
            $ChargeFee = $this->PaymentInfo["ChargeFee"]??0;
            $this->PaymentSubtotalFee = $this->AfterCouponTotal * $PaymentChargePercent / 100 + $ChargeFee;
            //金流方式 有限制不能寄送低溫包裹
            if ($this->PaymentInfo["DeliveryFrozen"]=="N") {
                foreach ($List as $key => $Data) {
                    if ($Data["DeliveryFrozen"]=="Y") {
                        $this->ErrorMessage = "[".$Data["Title"]."]為低溫包裹無法使用此金流方式";
                        return false;
                    }
                }
            }
        }
        /**物流**/
        $this->ShippingInfo = [];
        if ($ShippingID) {
            $oShipping = new \App\Models\Shipping\Shipping();
            $oShipping->where("Status", "Y");
            $this->ShippingInfo = $oShipping->find($ShippingID);
            if (!$this->ShippingInfo) {
                $this->ErrorMessage = "物流選項錯誤";
                return false;
            }
            //檢查商品總體積是否超過
            $DeliverVolumeMax = $this->ShippingInfo["DeliverVolumeMax"]??0;
            if ($this->TotalDeliverVolume > $DeliverVolumeMax) {
                $this->ErrorMessage = "商品總體積(".$this->TotalDeliverVolume.")超過物流限制(".$DeliverVolumeMax.")";
                return false;
            }
            //檢查商品總重量是否超過
            $DeliverWeightMax = $this->ShippingInfo["DeliverWeightMax"]??0;
            if ($this->TotalDeliverWeight > $DeliverWeightMax) {
                $this->ErrorMessage = "商品總重量(".$this->TotalDeliverWeight.")超過物流限制(".$DeliverWeightMax.")";
                return false;
            }
            //物流方式 有限制不能寄送低溫包裹
            if ($this->ShippingInfo["DeliveryFrozen"]=="N") {
                foreach ($List as $key => $Data) {
                    if ($Data["DeliveryFrozen"]=="Y") {
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
        if ($this->PaymentInfo && $this->ShippingInfo) {
            if (in_array($this->PaymentInfo["PaymentType"], ["FAMIC2C","UNIMARTC2C"], true)) {
                if ($this->ShippingInfo["ShippingType"]!=$this->PaymentInfo["PaymentType"]) {
                    $this->ErrorMessage = "貨到付款金物流方式為固定選項";
                    return false;
                }
            }
        }
        /**最後費用計算**/
        if ($this->ShippingFree) {
            $this->FinalTotal = $this->AfterCouponTotal + $this->PaymentSubtotalFee;
            $this->FinalTotalOutlying = $this->FinalTotal;
        } else {
            $this->FinalTotal = $this->AfterCouponTotal + $this->PaymentSubtotalFee + $this->ShippingFee;
            $this->FinalTotalOutlying = $this->AfterCouponTotal + $this->PaymentSubtotalFee + $this->ShippingFeeOutlying;
        }
        //
        $this->CheckoutList = $List;
        return $List;
    }

    //針對Str1所包含的資料，一一檢查是否同時出現在Str2之中
    protected function compareSeparatorString(string $Str1, string $Str2, string $Separator = ",")
    {
        $Str1 = trim($Str1);
        $Str2 = trim($Str2);
        if ($Str1 == "" && $Str2 == "") {
            return true;
        }
        if (($Str1 == "" && $Str2 != "") || ($Str1 != "" && $Str2 == "")) {
            return false;
        }
        //將逗號分隔字串轉換成陣列後比對兩者是否相同
        $ResultArray = [];
        $Array1 = explode($Separator, $Str1);
        $Array2 = explode($Separator, $Str2);
        foreach ($Array2 as $key2 => $Data2) {
            $ResultArray[$key2] = "N";
            foreach ($Array1 as $Data1) {
                if ($Data1 == $Data2) {
                    $ResultArray[$key2] = "Y";
                    break;
                }
            }
        }
        //查看比對結果
        foreach ($ResultArray as $Data) {
            if ($Data == "N") {
                return false;
            }
        }

        return true;
    }
}
