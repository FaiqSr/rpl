<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolsDetail extends BaseModel
{
    use HasFactory;


    public function tools(): BelongsTo
    {
        return $this->belongsTo(Tools::class, 'tools_id', 'tools');
    }
}
