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
}
