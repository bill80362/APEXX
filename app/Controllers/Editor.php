<?php

namespace App\Controllers;

use stdClass;

class Editor extends BaseController
{
    public function a1()
    {
        echo 123;
    }
    public function editor()
    {
//        try {
//            $response = \FroalaEditor_Image::upload('/editor/');
        ////            $cmp_image = (new imgcompress(SITE_PATH.'/portal/editor/' . explode("/", $response->link)[3], 1))->compressImg(SITE_PATH.'/portal/editor/' . explode("/", $response->link)[3]);
//            $response->link = $_ENV["app.baseURL"] . $response->link;
//            echo stripslashes(json_encode($response));
//        } catch (\Exception $e) {
//            http_response_code(404);
//        }

        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'PUT, PATCH, POST, GET, DELETE, OPTIONS');

        // Allowed extentions.
        $allowedExts = ["gif", "jpeg", "jpg", "png","webp"];

        // Get filename.
        $temp = explode(".", $_FILES["file"]["name"]);

        // Get extension.
        $extension = end($temp);

        // An image check is being done in the editor but it is best to
        // check that again on the server side.
        // Do not use $_FILES["file"]["type"] as it can be easily forged.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["file"]["tmp_name"]);

        if ((($mime == "image/gif")
                || ($mime == "image/jpeg")
                || ($mime == "image/pjpeg")
                || ($mime == "image/x-png")
                || ($mime == "image/webp")
                || ($mime == "image/png"))
            && in_array($extension, $allowedExts, true)) {
            // Generate new random name.
            $name = sha1(microtime()) . "." . $extension;

            // Save file in the uploads folder.
            move_uploaded_file($_FILES["file"]["tmp_name"], getcwd() . "/editor/" . $name);

            // Generate response.
            $response = new StdClass();
            $response->link = "/editor/" . $name;
            echo stripslashes(json_encode($response));
        }
    }
    public function getOptions()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'PUT, PATCH, POST, GET, DELETE, OPTIONS');

        echo 1;
    }
}
