<?php

namespace App\Libraries;

class LoginAdmin
{
    protected $ID = 0;
    protected $Info = [];
    public function setID($ID)
    {
        $this->ID = $ID;
    }
    public function getID()
    {
        return $this->ID;
    }
//    public function getInfo(){
//        if( count($this->Info) > 0 ){
//            return $this->Info;
//        }
        //抓AdminInfo
//    }

}
