<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tools extends BaseModel
{
    use HasFactory;

    protected $table = 'tools';
    protected $primaryKey = 'tool_code';


    public function details(): HasMany
    {
        return $this->hasMany(ToolsDetail::class, 'tool_code', 'tool_code');
    }
}
