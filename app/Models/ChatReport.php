<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatReport extends Model
{
    use HasFactory;

    public $fillable = ['message_id'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function question(): Message{
        return Message::where("id", "<", $this->message->id)
        ->where("role", Message::ROLE_USER)
        ->latest();
    }

    public function embeddings() {
        return Embedding::whereIn("id", json_encode($this->message->embeddings_ids))
        ->get();
    }
}
