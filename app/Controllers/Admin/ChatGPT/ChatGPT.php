<?php

namespace App\Controllers\Admin\ChatGPT;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class ChatGPT extends BaseController
{
    use ResponseTrait;
    public function getCompletions()
    {
        //
        $oDataColumn = new \App\Models\DataColumn();
        $oDataColumn->where("Title", "ChatGPTKey");
        $Data = $oDataColumn->first();
        $ChatGPTKey = $Data["Content"]??"";
        if (!$ChatGPTKey) {
            return $this->respond(ResponseData::fail("請先在通用欄位設定CharGPT的Key"));
        }
        //
        $model = $this->request->getVar("model");
        $prompt = $this->request->getVar("prompt");
        $max_tokens = $this->request->getVar("max_tokens");
        $temperature = $this->request->getVar("temperature");
        $top_p = $this->request->getVar("top_p");
        $JsonBody = [
            "model" => $model,
            "prompt" => $prompt,
            "max_tokens" => (int)$max_tokens,
            "temperature" => (int)$temperature,
            "top_p" => (int)$top_p,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.openai.com/v1/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($JsonBody),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$ChatGPTKey,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $response = json_decode($response,true);
        curl_close($curl);
        //Res
        return $this->respond(ResponseData::success($response));
    }

}
