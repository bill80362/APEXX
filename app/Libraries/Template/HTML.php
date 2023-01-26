<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\Template;

class HTML
{
    public $Attr = [];
    public function setAttr($Attr)
    {
        $this->Attr = $Attr;
    }
    public function getData($FileName)
    {
        //
        $text = file_get_contents(__DIR__."/".$FileName);
        //
        foreach ($this->Attr as $key => $value) {
            $text = str_replace($key, $value, $text);
        }
        //
        return $text;
    }
}
