<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\ResponseData;
use CodeIgniter\API\ResponseTrait;

class Question extends BaseController
{
    use ResponseTrait;
    public function getList(){
        $oQuestion = new \App\Models\Question\Question();
        $oQuestion->select("Question.*");
        $oQuestion->select("QuestionCategory.Title AS CategoryTitle");
        $oQuestion->join("QuestionCategory","QuestionCategory.QuestionCategoryID=Question.QuestionCategoryID");
        $oQuestion->orderBy("QuestionCategory.Seq","ASC");
        $oQuestion->orderBy("Question.Seq","ASC");
        $List = $oQuestion->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
    public function getCategoryList(){
        $oCategory = new \App\Models\Question\Category();
        $oCategory->orderBy("Seq","ASC");
        $List = $oCategory->findAll();
        //Res
        return $this->respond(ResponseData::success($List));
    }
}
