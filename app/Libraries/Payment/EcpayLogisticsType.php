<?php

namespace App\Libraries\Payment;

abstract class EcpayLogisticsType {
    const CVS = 'CVS';// 超商取貨
    const HOME = 'Home';// 宅配
}