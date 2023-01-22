<?php

namespace App\Libraries\Payment;

abstract class EcpayIsCollection {
    const YES = 'Y';// 貨到付款
    const NO = 'N';// 僅配送
}