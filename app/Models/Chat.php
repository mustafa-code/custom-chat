<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Chat extends Model
{
    use HasFactory;

    public $fillable = ['title'];
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function duration(){
        $first_message = $this->messages()->first();
        $last_message = $this->messages()->orderBy("id", "DESC")->first();

        if($last_message && $first_message){
            return $first_message->created_at->diffForHumans($last_message->created_at);
        }
        return "00:00";
    }

    public function requestsCount(){
        return $this->messages()->where("role", Message::ROLE_BOT)->count();
    }
}
