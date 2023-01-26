<?php

namespace App\Controllers\Admin\Member;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Member extends BaseController
{
    use ResponseTrait;
    public function getList()
    {
        //Filter

        //
        $Page = $this->request->getGet("Page")??1;
        $PageLimit = $this->request->getGet("PageLimit")??30;
        //
        $oMember = new \App\Models\Member\Member();
        //Filter

        //
        $oMember->orderBy("MemberID", "DESC");
        $Count = $oMember->countAllResults(false);
        $List = $oMember->paginate($PageLimit, "", $Page);
        //
        foreach ($List as $key => $Data) {
        }

        //Res
        $ResData = [
            "List"=>$List,
            "Count"=>$Count,
        ];
        return $this->respond(ResponseData::success($ResData));
    }
}
