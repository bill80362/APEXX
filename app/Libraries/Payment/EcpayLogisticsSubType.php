<?php

namespace App\Libraries\Payment;

abstract class EcpayLogisticsSubType
{
    public const TCAT = 'TCAT';// 黑貓(宅配)
    public const ECAN = 'ECAN';// 宅配通
    public const FAMILY = 'FAMI';// 全家
    public const UNIMART = 'UNIMART';// 統一超商
    public const HILIFE = 'HILIFE';// 萊爾富
    public const FAMILY_C2C = 'FAMIC2C';// 全家店到店
    public const UNIMART_C2C = 'UNIMARTC2C';// 統一超商寄貨便
    public const HILIFE_C2C = 'HILIFEC2C';// 萊爾富富店到店
}
