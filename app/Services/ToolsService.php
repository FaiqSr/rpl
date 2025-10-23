<?php


namespace App\Services;

use App\Models\Tools;

class ToolsService extends Service
{

    protected $toolsModel;
    public function __construct(Tools $toolsModel)
    {
        $this->toolsModel = $toolsModel;
    }


    function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
