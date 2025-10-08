<div class="max-w-6xl mx-auto p-6 space-y-6">

  {{-- Header --}}
  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold">Happy — Mes habitudes</h1>
      <p class="opacity-70 text-sm">Crée, reprends ou arrête des habitudes. Affichage classique en cartes.</p>
    </div>

    {{-- Formulaire création --}}
    <form wire:submit.prevent="addHabit" class="flex gap-3 items-end">
      <div>
        <label class="block text-sm">Nom</label>
        <input type="text" wire:model.defer="name" class="input input-bordered w-64"
               placeholder="7000 pas / Arrêter de fumer">
        @error('name')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-sm">Type</label>
        <select wire:model.defer="type" class="select select-bordered">
          <option value="positive">Positive</option>
          <option value="stop">Arrêt</option>
        </select>
      </div>
      <button class="btn btn-primary">Ajouter</button>
    </form>
  </div>

  {{-- Filtres / Recherche --}}
  <div class="flex flex-wrap items-center gap-3">
    <div role="tablist" class="tabs tabs-boxed">
      <button class="tab {{ $scope==='active' ? 'tab-active' : '' }}" wire:click="setScope('active')">Actives</button>
      <button class="tab {{ $scope==='archived' ? 'tab-active' : '' }}" wire:click="setScope('archived')">Archivées</button>
      <button class="tab {{ $scope==='all' ? 'tab-active' : '' }}" wire:click="setScope('all')">Toutes</button>
    </div>
    <label class="input input-bordered flex items-center gap-2 ml-auto">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M..." /></svg>
      <input type="text" class="grow" placeholder="Rechercher…" wire:model.debounce.400ms="q">
    </label>
  </div>

  {{-- Grille de cartes --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($habits as $h)
      @php
        $streak = $h->currentStreakDays();
        $best   = $h->bestStreakDays();
        $badge  = $h->type === 'positive' ? 'badge-success' : 'badge-warning';
      @endphp

      <div class="card bg-base-100 shadow hover:shadow-lg transition">
        <div class="card-body p-4">
          <div class="flex items-center justify-between">
            <h3 class="card-title text-base">{{ $h->name }}</h3>
            <span class="badge {{ $badge }} capitalize">{{ $h->type }}</span>
          </div>

          <p class="text-sm opacity-80 mt-1">
            🔥 <b>{{ $streak }}</b> j — 🏆 {{ $best }} j
          </p>

          {{-- Actions --}}
          <div class="card-actions mt-3 justify-between">
            @if($h->currentPeriod())
              <button class="btn btn-warning btn-sm" wire:click="stopHabit({{ $h->id }})">Arrêter</button>
            @else
              <button class="btn btn-success btn-sm" wire:click="startHabit({{ $h->id }})">Reprendre</button>
            @endif

            <div class="flex gap-2">
              @if($h->is_active)
                <button class="btn btn-outline btn-sm" wire:click="archiveHabit({{ $h->id }})">Archiver</button>
              @else
                <button class="btn btn-outline btn-sm" wire:click="restoreHabit({{ $h->id }})">Restaurer</button>
              @endif
              <button class="btn btn-ghost btn-sm" wire:click="deleteHabit({{ $h->id }})">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-span-full">
        <div class="alert">
          <span>Aucune habitude pour l’instant — ajoute ta première juste au-dessus.</span>
        </div>
      </div>
    @endforelse
  </div>

</div>
