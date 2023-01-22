<?php

namespace App\Libraries\Payment;

abstract class EcpayUrl {
    const CVS_MAP = '/Express/map';// 電子地圖
    const SHIPPING_ORDER = '/Express/Create';// 物流訂單建立
    const HOME_RETURN_ORDER = '/Express/ReturnHome';// 宅配逆物流訂單
    const UNIMART_RETURN_ORDER = '/express/ReturnUniMartCVS';// 超商取貨逆物流訂單(統一超商B2C)
    const HILIFE_RETURN_ORDER = '/express/ReturnHiLifeCVS';// 超商取貨逆物流訂單(萊爾富超商B2C)
    const FAMILY_RETURN_ORDER = '/express/ReturnCVS';// 超商取貨逆物流訂單(全家超商B2C)
    const FAMILY_RETURN_CHECK = '/Helper/LogisticsCheckAccoounts';// 全家逆物流核帳(全家超商B2C)
    const UNIMART_UPDATE_LOGISTICS_INFO = '/Helper/UpdateShipmentInfo';// 統一修改物流資訊(全家超商B2C)
    const UNIMART_UPDATE_STORE_INFO = '/Express/UpdateStoreInfo';// 更新門市(統一超商C2C)
    const UNIMART_CANCEL_LOGISTICS_ORDER = '/Express/CancelC2COrder';// 取消訂單(統一超商C2C)
    const QUERY_LOGISTICS_INFO = '/Helper/QueryLogisticsTradeInfo/V2';// 物流訂單查詢
    const PRINT_TRADE_DOC = '/helper/printTradeDocument';// 產生托運單(宅配)/一段標(超商取貨)
    const PRINT_UNIMART_C2C_BILL = '/Express/PrintUniMartC2COrderInfo';// 列印繳款單(統一超商C2C)
    const PRINT_FAMILY_C2C_BILL = '/Express/PrintFAMIC2COrderInfo';// 全家列印小白單(全家超商C2C)
    const Print_HILIFE_C2C_BILL = '/Express/PrintHILIFEC2COrderInfo';// 萊爾富列印小白單(萊爾富超商C2C)
    const CREATE_TEST_DATA = '/Express/CreateTestData';// 產生 B2C 測標資料
}