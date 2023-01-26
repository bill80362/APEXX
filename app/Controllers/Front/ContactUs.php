<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class ContactUs extends BaseController
{
    use ResponseTrait;
    public function send()
    {
        //
        $Name = $this->request->getVar("Name");
        $Phone = $this->request->getVar("Phone");
        $Email = $this->request->getVar("Email");
        $Comment = $this->request->getVar("Comment");

        //製作樣板
//        $oLibHTML = new \App\Libraries\Template\HTML();
//        $oLibHTML->setAttr([
//            '##Name' => $Name,
//            '##Phone' => $Phone,
//            '##Email' => $Email,
//            '##Comment' => $Comment,
//            '##SendDate' => date("Y-m-d"),
//        ]);
//        $HTML_Contact = $oLibHTML->getData("Contact.html");//給客戶
//        $HTML_ContactUs = $oLibHTML->getData("Contact_us.html");//給廠商
        $ViewData = [
            'Name' => $Name,
            'Phone' => $Phone,
            'Email' => $Email,
            'Comment' => $Comment,
            'SendDate' => date("Y-m-d"),
        ];
        $HTML_Contact = view('/Mail/Contact', $ViewData);
        $HTML_ContactUs = view('/Mail/Contact_us', $ViewData);
        //寄信
        $oMail = new \App\Libraries\Tools\Mail();
        $oMail->send($Email, "網站聯絡表單", $HTML_Contact);//給客戶
        $oMail->send($oMail->GmailUsername, "網站聯絡表單", $HTML_ContactUs);//給廠商
        //Res
        return $this->respond(ResponseData::success([]));
    }
}
