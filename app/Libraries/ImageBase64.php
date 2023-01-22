<?php

namespace App\Libraries;

class ImageBase64
{
    //Image轉成Base64
    static public function Image_to_Base64($_Path){
        $type = pathinfo($_Path, PATHINFO_EXTENSION);
        $data = file_get_contents($_Path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
    //Base64轉成Image
    static public function Base64_to_Image($_Base64String,$_Path){
        @list(, $data) = explode(';', $_Base64String);
        @list(, $data) = explode(',', $data);
        $data = base64_decode($data);
//        exit();
        file_put_contents($_Path, $data,FILE_APPEND);
        chmod($_Path, 0664);
        return true;
    }
    //Base64圖片類型
    static public function What_Is_Base64Image_Type($_Base64String){
        if(strpos($_Base64String,'jpg') !== false){
            return "jpg";
        }elseif(strpos($_Base64String,'jpeg') !== false){
            return "jpg";
        }elseif(strpos($_Base64String,'png') !== false){
            return "png";
        }elseif(strpos($_Base64String,'gif') !== false){
            return "gif";
        }else{
            return "jpg";
        }
    }
    //上傳圖片工具
    static public function Base64_UploadFile($_Base64String,$SubDir){
        helper('text');
        $ImageType = ImageBase64::What_Is_Base64Image_Type($_Base64String);
        $FilePath = $SubDir."/".random_string('md5').".".$ImageType;//檔案路徑
        $HostImagePath = ROOTPATH."pubilc/image/".$FilePath;//本機儲存位置
        $rs = ImageBase64::Base64_to_Image($_Base64String,$HostImagePath);
        if($rs){
            return $FilePath;
        }else{
            return false;
        }
    }


}
