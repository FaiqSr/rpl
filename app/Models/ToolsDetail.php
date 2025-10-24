<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolsDetail extends BaseModel
{
    use HasFactory;

    protected $table = 'tool_details';
    protected $primaryKey = 'tool_detail_id';

    protected $fillable = [
        'tool_code',
        'serial_number',
        'condition',
        'purchase_date',
        'price',
        'location',
        'status'
    ];



    public function tools(): BelongsTo
    {
        return $this->belongsTo(Tools::class, 'tool_code', 'tool_code');
    }
}
