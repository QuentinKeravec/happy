<div>
    <div
      x-data="{ toasts: [] }"
      x-init="
        window.addEventListener('toast', e => {
          const t = { id: Date.now(), ...e.detail };
          toasts.push(t);
          setTimeout(() => { toasts = toasts.filter(x => x.id !== t.id) }, 3000);
        });
      "
      class="fixed right-4 bottom-4 space-y-2 z-[9999]"  {{-- 👈 ici bottom-4 au lieu de top-4 --}}
    >
      <template x-for="t in toasts" :key="t.id">
        <div class="alert shadow-lg"
             :class="{
               'alert-success': t.type === 'success',
               'alert-error'  : t.type === 'error',
               'alert-warning': t.type === 'warning',
               'alert-info'   : !['success','error','warning'].includes(t.type)
             }">
          <span x-text="t.message"></span>
        </div>
      </template>
    </div>

    <div class="max-w-6xl mx-auto p-6 space-y-6">

      {{-- Header --}}
      <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-950">Happy — Mes habitudes</h1>
          <p class="opacity-70 text-sm text-gray-950">Crée, reprends ou arrête des habitudes.</p>
        </div>

        {{-- Formulaire création --}}
        <form id="habit-form"
              wire:submit.prevent="addHabit"
              class="flex gap-3 items-end">

          <div>
            <label class="block text-sm text-gray-950">Nom</label>
            <input
              wire:key="name-input-{{ $formKey }}"
              wire:model.live="name"
              autocomplete="off"
              type="text"
              class="input input-bordered w-64 bg-sky-50 border-gray-600 text-gray-950"
              placeholder="7000 pas / Arrêter de fumer">
            @error('name')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm text-gray-950">Type</label>
            <select
              wire:key="type-select-{{ $formKey }}"
              wire:model.live="type"
              class="select select-bordered bg-sky-50 border-gray-600 text-gray-950">
              <option value="positive">Positive</option>
              <option value="stop">Arrêt</option>
            </select>
          </div>

          <div>
            <label class="block text-sm">Date de départ</label>
            <input type="date"
                   wire:key="date-input-{{ $formKey }}"
                   wire:model.live="started_at"
                   max="{{ now()->toDateString() }}"
                   class="input input-bordered bg-sky-50 border-gray-600 text-gray-950">
            @error('started_at')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
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
        <label class="input input-bordered flex items-center gap-2 ml-auto bg-sky-50 border-gray-600 text-gray-950">
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

          <div class="card bg-emerald-600 shadow hover:shadow-lg transition" wire:key="habit-card-{{ $h->id }}">
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
                   <button type="button" class="btn btn-outline btn-sm"
                           wire:click="openCalendar({{ $h->id }})">
                     Voir le calendrier
                   </button>
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

    @if($calendarOpen && $calendar)
    <div class="modal modal-open">
      <div class="modal-box max-w-3xl">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-lg">
            {{ $calendar['habit']->name }} — {{ ucfirst($calendar['monthLabel']) }}
          </h3>
          <div class="flex gap-2">
            <button class="btn btn-sm" wire:click="prevMonth" @disabled(! $calendar['canPrev'])>←</button>
            <button class="btn btn-sm" wire:click="nextMonth" @disabled(! $calendar['canNext'])>→</button>
          </div>
        </div>

        {{-- Légende --}}
        <div class="flex items-center gap-3 mb-3 text-sm">
          <span class="inline-block w-4 h-4 rounded bg-success"></span> Succès
          <span class="inline-block w-4 h-4 rounded bg-base-300"></span> Non tenu
          <span class="ml-auto badge">{{ $calendar['habit']->type }}</span>
        </div>

        {{-- En-têtes jours (Lun → Dim) --}}
        <div class="grid grid-cols-7 gap-1 text-xs font-semibold opacity-70 mb-1">
          <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
        </div>

        {{-- Grille calendrier --}}
        <div class="grid grid-cols-7 gap-1">
          {{-- offsets avant le 1er du mois --}}
          @for($i=0; $i < $calendar['lead']; $i++)
            <div class="h-10 rounded bg-transparent"></div>
          @endfor

          @foreach($calendar['days'] as $d)
            <div class="h-10 rounded grid place-items-center text-sm
                        {{ $d['active'] ? 'bg-success text-success-content' : 'bg-base-300' }}
                        {{ $d['isToday'] ? 'ring-2 ring-primary' : '' }}">
              {{ $d['day'] }}
            </div>
          @endforeach
        </div>

        <div class="modal-action">
          <button class="btn" wire:click="$set('calendarOpen', false)">Fermer</button>
        </div>
      </div>
    </div>
    @endif
</div>
