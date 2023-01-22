<?php
/**
 * Created by PhpStorm.
 * User: Win10_User
 * Date: 2021/6/17
 * Time: 上午 11:23
 */

namespace App\Libraries\Tools;

class DatabaseTools
{
	//一個KEY只有一個Value
	static public function ListToKV(Array $DataList,$KeyName){
		
		$DataKV = [];
		foreach ($DataList as $value){
			$DataKV[$value[$KeyName]] = $value;
		}
		return $DataKV;
	}
	//一個KEY有多個Value
	static public function ListToKVMultiple(Array $DataList,$KeyName){
		$DataKV = [];
		foreach ($DataList as $value){
			$DataKV[$value[$KeyName]][] = $value;
		}
		return $DataKV;
	}
}