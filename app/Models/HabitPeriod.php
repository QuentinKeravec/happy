<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HabitPeriod extends Model
{
    protected $fillable = ['habit_id','started_at','ended_at'];
    protected $casts = ['started_at'=>'date', 'ended_at'=>'date'];

    // Durée en jours (inclusif), ended_at NULL => aujourd'hui
    public function durationDays(): int {
        $start = $this->started_at->copy()->startOfDay();
        $end   = ($this->ended_at ?? now())->copy()->startOfDay();
        return max(1, $start->diffInDays($end) + 1); // inclure le jour de début
    }

    public function habit()
    {
        return $this->belongsTo(\App\Models\Habit::class);
    }
}
