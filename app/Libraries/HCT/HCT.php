<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\HCT;

class HCT
{
    public $PreCode = "HCT";
//    public $Company = "test";
//    public $Password = "test1";
//    public $URL = "http://hctrt.hct.com.tw";
    public $Company = "";
    public $Password = "";
    public $URL = "";
    //
    public function __construct()
    {
        //
        $this->Company = $_ENV["HCT.Company"];
        $this->Password = $_ENV["HCT.Password"];
        $this->URL = $_ENV["HCT.URL"];
        //
        helper('text');
        $this->PreCode = random_string('alnum', 5);
    }
    //
    public function sendData($TradeID, $ReceiverName, $ReceiverPhone, $ReceiverAddress, $Price, $Weight)
    {
        $arrContextOptions=["ssl"=>[ "verify_peer"=>false, "verify_peer_name"=>false,'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT]];
        $options = [
            'soap_version'=>SOAP_1_2,
            'exceptions'=>true,
            'trace'=>1,
            'cache_wsdl'=>WSDL_CACHE_NONE,
            'stream_context' => stream_context_create($arrContextOptions)
        ];
        $client = new \SoapClient($this->URL."/EDI_WebService2/Service1.asmx?WSDL", $options);

        $List = [];
        $List[] = [
            //必要欄位
            'epino'=>$this->PreCode.$TradeID,//訂單編號
            'ercsig'=>$ReceiverName,//收貨人名稱
            'ertel1'=>$ReceiverPhone,//收貨人電話 1
            'eraddr'=>$ReceiverAddress,//收貨人地址
            'ejamt'=>$Price,//件數 必要欄位(最小為 1)
            'eqamt'=>$Weight,//重量 如未提供則使用帳號預設值(小數進位到 整數)
            //非必要
//            'esdate'=>'',//出貨日期 預設今天(YYYYMMDD)
//            'ertel2'=>'0910806046',//收貨人電話 2
//            'escsno'=>'',//客代
//            'esstno'=>'8039987012',//出貨站
//            'edelno'=>'8983219626',//新竹貨號 如無提供系統自行配號
//            'etcsig'=>'',
//            'ettel1'=>'',
//            'ettel2'=>'',
//            'etaddr'=>'',
//            'eddate'=>'',
//            'eqmny'=>'',
//            'EMARK'=>'',//備註
        ];

        $param_ary =  ['company'=>$this->Company,'password'=>$this->Password,'json'=> json_encode($List)  ] ;

        $aryResult =$client->__soapCall('TransData_Json', ['parameters' => $param_ary]);
        $ResponseData = json_decode($aryResult->TransData_JsonResult, true);
//        print($aryResult->TransData_JsonResult);
        return $ResponseData;
    }
    public function getLabelImage($str)
    {
        $img = $this->hexToStr($str);
        return $img;
    }
    public function getShippingInfo($HCT_No)
    {
        //新竹貨運給的加密規則
        $EncryptIV = "QCSVRDHT";
        $EncryptKey = date("Ymd", strtotime("-357 day"));
        $no = $this->encrypt($HCT_No, $EncryptKey, $EncryptIV);
        //新竹貨運給的 v
        $v = "487E08E062A8CA61013B07B90B99839A";
        //
        $URL = "https://hctapiweb.hct.com.tw/phone/searchGoods_Main.aspx?no=".$no."&v=".$v;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rs = curl_exec($ch);
        curl_close($ch);
        return $rs;
    }
    //文字轉圖片
    protected function strToHex($str)
    {
        $hex = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $hex .= dechex(ord($str[$i]));
        }
        return $hex;
    }
    protected function hexToStr($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex)-1; $i += 2) {
            $str .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $str;
    }
    //
    /*
    * 在采用DES加密算法,cbc模式,pkcs5Padding字符填充方式下,对明文进行加密函数
    */
    public function encrypt($input, $ky, $iv)
    {
        $key = $ky;
//        $iv = $iv;  //$iv为加解密向量
        $size = 8; //填充块的大小,单位为bite    初始向量iv的位数要和进行pading的分组块大小相等!!!
        $input = $this->pkcs5_pad($input, $size);  //对明文进行字符填充
        $encOpenssl = openssl_encrypt($input, "des-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        $data = base64_encode($encOpenssl);
        return $data;
    }

    /*
    * 在采用DES加密算法,cbc模式,pkcs5Padding字符填充方式,对密文进行解密函数
    */
    public function decrypt($crypt, $ky, $iv)
    {
        $crypt = base64_decode($crypt, true);   //对加密后的密文进行解base64编码
        $key = $ky;
        $iv = $iv;  //$iv为加解密向量
        $td = mcrypt_module_open(MCRYPT_DES, '', 'cbc', '');    //MCRYPT_DES代表用DES算法加解密;'cbc'代表使用cbc模式进行加解密.
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_data = mdecrypt_generic($td, $crypt);    //对$input进行解密
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $decrypted_data = $this->pkcs5_unpad($decrypted_data); //对解密后的明文进行去掉字符填充
        $decrypted_data = rtrim($decrypted_data);   //去空格
        return $decrypted_data;
    }
    /*
    * 对明文进行给定块大小的字符填充
    */
    public function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    /*
    * 对解密后的已字符填充的明文进行去掉填充字符
    */
    public function pkcs5_unpad($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}
