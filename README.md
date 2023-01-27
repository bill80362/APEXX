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

# 金流
- 目前只有建立訂單，沒有查詢訂單

# 常駐程式
- 放在 /app/Commands，主機目前只能透過Crontab定期執行


測試測試