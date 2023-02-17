# 公版
- 請將所有帳密都統一放在.env
- composer run-script fix-code 格式化程式碼
- IDE可以設定php-cs-fixer路徑在/vendor/bin/php-cs-fixer

# 資料庫
- 資料庫新增欄位，預設固定都盡量使用Null讓新增沒有Error
- 驗證DB欄位寫入統一在php的model設定，方便專案擴充

# 產品庫存
- 由產品、顏色、尺寸組成
- 訂單下單，訂單商品內放的是商品庫存

# 自訂商品
- 一個自訂商品，可以設定多樣化規格，一組是一種庫存，但是沒有數量考量。
- 同樣根據商品id吃到menu id 吃到 discount 的優惠設定

# 金流(前只有建立訂單，沒有查詢訂單)
- 綠界信用卡、ATM、超商代碼
- 綠界 貨到付款 全家 7-11
- 街口支付
- LinePay
- PCHomePay信用卡、ATM、
- 無串接 NormalATM

# 常駐程式
- 放在 /app/Commands，主機目前只能透過Crontab定期執行

# 結帳程式流程(按順序)

#### =====初始檢查=====
- 庫存狀態：關閉
- 商品狀態：關閉
- 已無庫存
- 如果商品時間都有設定(起迄)，商品是否超出銷售時間限制

#### =====商品優惠分類打折(有設定MenuID)=====

- 「優惠折扣的金額累積」
- 統計各個優惠，會考量符合MenuID對應商品ID，才會列入統計。

- 「購物車內各個商品吃到的優惠折扣」
- 計算購物車內每個商品，針對各個優惠的金額累積，比較優惠門檻，吃到的最低優惠折扣數(DiscountPercentMenu)，吃到最低優惠的資訊(DiscountPercentMenuInfo)

- 統計到此階段「商品優惠分類折扣後的金額加總」(DiscountMenuTotal)

#### =====全館優惠打折(無設定MenuID)=====
- 「優惠折扣的金額累積」(購物車各個商品的CheckoutPrice為統計全館優惠累積)
- 根據設定的MenuID應商品ID，才會列入統計全館優惠累積(設定CheckoutPrice = DiscountMenuTotal)
- 全館折扣統計完畢之後有符合到門檻的，各購物車商品會計入
- DiscountPercentFull 全館折扣
- DiscountPercentFullInfo 吃到的優惠ID

- PS 全館優惠打折，可能有Bug，沒有設定MenuID會是全館，但是全館統計又要設定MenuID才列入統計，導致會根本無作用。

- 各個商品小計為 DiscountPrice = 售價x分類打折x全館打折 (打折會堆疊)
- 統計到此階段「全館優惠打折折扣後的金額加總」(DiscountFullTotal)

#### =====現金折抵優惠券=====
- 如果有填入優惠碼，會檢查優惠碼是否正確，沒有會被擋。
- 檢查包含時間起迄、使用門檻、優惠卷數量、是否限定會員
- CouponInfo 使用的抵用卷資訊
- 折抵優惠卷後的金額 AfterCouponTotal = DiscountFullTotal - 優惠卷折抵金額

#### =====是否有免運(無考慮MenuID)=====
- AfterCouponTotal 大於優惠免運設定的門檻
- ShippingFree 是否免運
- DiscountID_ShippingFree 使用優惠免運的優惠ID

#### =====是否有贈品(無考慮MenuID)=====
- GiveInfo 取得的贈品優惠資訊

#### =====計算退貨金額 平均分配 現金折抵優惠券 ====
- 購物車各個商品的退貨金額 RefundPrice
- 將現金折抵優惠券金額，平攤給每個商品，排前面的商品可能有多１元的可能

#### =====金流=====
- 檢查使用的金流，是否不能寄送低溫包裹。如果有，商品有低溫商品會擋。
- PaymentInfo 金流資訊
- PaymentSubtotalFee 金額額外費用 = AfterCouponTotal * 金額額外費用% + 金流額外固定費

#### =====物流=====
- 檢查商品加總的體積和重量，是否有超過所選的物流設定的體積和重量。
- 檢查使用的物流，是否不能寄送低溫包裹。如果有，商品有低溫商品會擋。
- ShippingInfo 物流資訊
- ShippingFee 境內物流費
- ShippingFeeOutlying 境外物流費
- ShippingStatusOutlying 境外是否可以使用

#### =====最後費用計算=====
 - 免運：
FinalTotal = AfterCouponTotal + PaymentSubtotalFee
FinalTotalOutlying = FinalTotal
 - 無免運：
FinalTotal = AfterCouponTotal + PaymentSubtotalFee + ShippingFee
FinalTotalOutlying = AfterCouponTotal + PaymentSubtotalFee + ShippingFeeOutlying



