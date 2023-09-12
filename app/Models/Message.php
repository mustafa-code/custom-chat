<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    use HasFactory;

    const ROLE_USER = "user";
    const ROLE_BOT = "assistant";
    const ROLE_FUNCTION = "function";

    public $fillable = ['chat_id', 'role', 'content', 'function_call', 'name', "embeddings_ids"];

    protected $jsonable = ["function_call", "embeddings_ids"];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(ChatReport::class);
    }

}
