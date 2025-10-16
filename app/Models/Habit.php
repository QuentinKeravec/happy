<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HabitPeriod;

class Habit extends Model
{
    protected $fillable = ['user_id','name','type','is_active', 'amount_per_day'];

    public function periods(): HasMany {
        return $this->hasMany(HabitPeriod::class)->orderBy('started_at');
    }

    public function currentPeriod(): ?HabitPeriod {
        return $this->periods()->whereNull('ended_at')->orderByDesc('started_at')->first();
    }

    // Streak actuelle (jours) = période en cours si existe
    public function currentStreakDays(): int {
        $p = $this->currentPeriod();
        if (!$p) return 0;
        return $p->durationDays(); // du début -> aujourd'hui
    }

    // Meilleure streak historique (max des durées)
    public function bestStreakDays(): int {
        // prend toutes les périodes, remplace ended_at NULL par today
        return $this->periods->map(fn($p) => $p->durationDays())->max() ?? 0;
    }

    // Démarrer/reprendre une habitude (crée une nouvelle période ouverte)
    public function start(string $date = null): HabitPeriod {
        // fermer une période ouverte au besoin (sécurité)
        if ($open = $this->currentPeriod()) {
            return $open; // déjà en cours -> rien à faire
        }
        return $this->periods()->create([
            'started_at' => $date ?? now()->toDateString(),
            'ended_at'   => null,
        ]);
    }

    // Arrêter (clôturer la période courante)
    public function stop(string $date = null): bool {
        $open = $this->currentPeriod();
        if (!$open) return false;
        $closeDate = $date ?? now()->toDateString();
        // garde ended_at >= started_at
        if ($closeDate < $open->started_at->toDateString()) $closeDate = $open->started_at->toDateString();
        return $open->update(['ended_at' => $closeDate]);
    }

    public function lastPeriod(): ?HabitPeriod
    {
        // si periods déjà chargé, utilise la collection; sinon, requête
        if ($this->relationLoaded('periods')) {
            return $this->periods->sortByDesc('started_at')->first();
        }
        return $this->periods()->orderByDesc('started_at')->first();
    }

    public function isStopped(): bool
    {
        $last = $this->lastPeriod();
        return $last ? !is_null($last->ended_at) : true; // aucun historique = considéré "à l'arrêt"
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
