<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tools extends BaseModel
{
    use HasFactory;

    protected $table = 'tools';
    protected $primaryKey = 'tool_id';


    public function toolsId(): HasMany
    {
        return $this->hasMany(ToolsDetail::class, 'tool_id', 'tool_id');
    }
}
