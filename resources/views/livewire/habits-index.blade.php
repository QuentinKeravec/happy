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
      class="fixed right-4 bottom-4 space-y-2 z-[9999]"
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
          <p class="opacity-70 text-sm font-bold">{{ __('messages.header') }}</p>
        </div>

        {{-- Formulaire création --}}
        <form id="habit-form"
              wire:submit.prevent="addHabit"
              class="flex gap-3 items-end">

          <div>
            <label class="block text-sm">{{ __('messages.habit_label') }}</label>
            <div class="indicator">
                <span class="indicator-item badge badge-primary">*</span>
                <input
                  wire:key="name-input-{{ $formKey }}"
                  wire:model.live="name"
                  autocomplete="off"
                  type="text"
                  class="input input-bordered w-64 @error('name') input-error @enderror"
                  placeholder="{{ __('messages.habit_example') }}">
            </div>
          </div>

          <div>
            <label class="block text-sm">{{ __('messages.type_label') }}</label>
            <select
              wire:key="type-select-{{ $formKey }}"
              wire:model.live="type"
              class="select select-bordered w-24">
              <option value="good_habit">{{ __('messages.good_habit') }}</option>
              <option value="bad_habit">{{ __('messages.bad_habit') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm">{{ __('messages.cost_label') }}</label>
            <input type="number" step="100" min="0"
                 wire:key="name-input-{{ $formKey }}"
                 wire:model="amount_per_day"
                 class="input input-bordered w-28"
                 placeholder="{{ __('messages.cost_example') }}">
          </div>

          <div x-data="{ fp: null }"
             x-init="
               fp = window.initDatepicker(
                 $refs.dp,
                 @js($started_at),
                 '{{ app()->getLocale() }}',
                 (sel) => $wire.set('started_at', sel[0] ? fp.formatDate(sel[0],'Y-m-d') : null)
               );
               // resync si Livewire modifie la valeur
               $watch('$wire.started_at', v => { if (v) fp.setDate(v, true) });
             ">
            <label class="block text-sm mb-1">{{ __('messages.date_label') }}</label>
            <div class="indicator">
                <span class="indicator-item badge badge-primary">*</span>
                <input x-ref="dp" type="text"
                     class="input input-bordered w-36 @error('name') input-error @enderror"
                     wire:key="name-input-{{ $formKey }}"
                     wire:model.live="started_at"
                     placeholder="YYYY-MM-DD">
            </div>
          </div>

          <button class="btn btn-success">{{ __('messages.add') }}</button>
        </form>
      </div>

       <div class="flex flex-wrap items-center gap-3">
          {{-- Filtres état --}}
          <div role="tablist" class="tabs tabs-boxed">
            <button type="button" class="tab {{ $scope==='active' ? 'tab-active' : '' }}"
                    wire:click="setScope('active')">{{ __('messages.active') }}</button>
            <button type="button" class="tab {{ $scope==='archived' ? 'tab-active' : '' }}"
                    wire:click="setScope('archived')">{{ __('messages.archived') }}</button>
            <button type="button" class="tab {{ $scope==='all' ? 'tab-active' : '' }}"
                    wire:click="setScope('all')">{{ __('messages.all') }}</button>
          </div>

        {{-- Tri --}}
        @php
          $labels = [
            'recent' => __('messages.most_recent'),
            'name'   => __('messages.alphabetic_order'),
            'streak' => __('messages.actual_streak'),
            'best'   => __('messages.best_record'),
          ];
        @endphp

        <details class="dropdown dropdown-end ml-auto z-50">
          <summary class="btn btn-sm gap-2">
            <span class="font-semibold">{{ $labels[$sort] ?? '—' }}</span>
            <svg class="w-4 h-4 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
          </summary>

          <ul class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-56">
            <li>
              <button type="button"
                      class="{{ $sort==='recent' ? 'active font-semibold' : '' }}"
                      wire:click="$set('sort','recent')">
                {{ __('messages.most_recent') }}
                @if($sort==='recent') <span class="badge badge-primary">✓</span> @endif
              </button>
            </li>
            <li>
              <button type="button"
                      class="{{ $sort==='name' ? 'active font-semibold' : '' }}"
                      wire:click="$set('sort','name')">
                {{ __('messages.alphabetic_order') }}
                @if($sort==='name') <span class="badge badge-primary">✓</span> @endif
              </button>
            </li>
            <li>
              <button type="button"
                      class="{{ $sort==='streak' ? 'active font-semibold' : '' }}"
                      wire:click="$set('sort','streak')">
                {{ __('messages.actual_streak') }}
                @if($sort==='streak') <span class="badge badge-primary">✓</span> @endif
              </button>
            </li>
            <li>
              <button type="button"
                      class="{{ $sort==='best' ? 'active font-semibold' : '' }}"
                      wire:click="$set('sort','best')">
                {{ __('messages.best_streak') }}
                @if($sort==='best') <span class="badge badge-primary">✓</span> @endif
              </button>
            </li>
          </ul>
        </details>

          {{-- Recherche --}}
          <label class="input input-bordered flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M..." /></svg>
            <input type="text" class="grow" placeholder="{{ __('messages.search') }}" wire:model.live="q">
          </label>
      </div>

      @php
        $locale    = app()->getLocale();
        $currency  = $locale === 'ja' ? 'JPY' : 'EUR';
        $precision = $currency === 'JPY' ? 0 : 2;
        $totalSaved = \Illuminate\Support\Number::currency($totalSavedRaw, $currency, $locale, $precision);
      @endphp

      <div class="stats stats-vertical lg:stats-horizontal shadow w-full bg-base-100 mb-4">
        <div class="stat">
          <div class="stat-title">{{ __('messages.money_saved') }}</div>
          <div class="stat-value">{{ $totalSaved }}</div>
          <div class="stat-desc">{{ __('messages.since_start') }}</div>
        </div>

        <div class="stat">
          <div class="stat-title">{{ __('messages.active_habits') }}</div>
          <div class="stat-value">{{ $activeCount }} {{ __('messages.counter') }}</div>
          <div class="stat-desc">{{ __('messages.running_now') }}</div>
        </div>

        <div class="stat">
          <div class="stat-title">{{ __('messages.best_streak') }}</div>
          <div class="stat-value">{{ $bestRecordDays }} {{ __('messages.days') }}</div>
          <div class="stat-desc">{{ __('messages.personal_record') }}</div>
        </div>
      </div>

      {{-- Spinner de chargement DaisyUI --}}
      <div class="flex justify-center my-4" wire:loading.flex wire:target="q,sort,scope">
        <span class="loading loading-spinner loading-lg text-primary"></span>
      </div>

      {{-- Grille de cartes --}}
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" wire:key="grid-{{ $scope }}-{{ $sort }}-{{ md5($q) }}">
        @forelse($habits as $h)
          @php
            $streak = $h->currentStreakDays();
            $best   = $h->bestStreakDays();
            $badge  = $h->type === 'good_habit' ? 'badge-success' : 'badge-error';
          @endphp

        <div class="card {{ $h->isStopped() ? 'bg-rose-600' : 'bg-emerald-600' }} shadow-md shadow-zinc-600 hover:shadow-lg hover:shadow-zinc-600  transition"
             wire:key="habit-card-{{ $h->id }}">

          {{-- Croix de suppression, petite, coin haut-droit --}}
          <button
            type="button"
            class="btn btn-circle btn-ghost btn-xs absolute top-2 right-2 z-10 text-base-200 hover:text-white hover:bg-black/20"
            wire:click="deleteHabit({{ $h->id }})"
            title="{{ __('messages.delete') }}"
          >
            ✕
          </button>

          <div class="card-body p-4 sm:p-6 flex flex-col justify-between h-full">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between relative pr-6">
              <div class="min-w-0 space-y-1">
                <h3 class="card-title text-base sm:text-lg font-semibold truncate">
                  {{ $h->name }}
                </h3>

                @php
                  $locale    = app()->getLocale();
                  $currency  = $locale === 'ja' ? 'JPY' : 'EUR';
                  $precision = $currency === 'JPY' ? 0 : 2;
                  $perDayVal = $h->amount_per_day ?? 0;
                  $totalVal  = $perDayVal * $h->currentStreakDays();
                  $perDay    = \Illuminate\Support\Number::currency($perDayVal, $currency, $locale, $precision);
                  $total     = \Illuminate\Support\Number::currency($totalVal,  $currency, $locale, $precision);
                @endphp

                @if($h->amount_per_day)
                  <p class="text-sm opacity-90">💰 {{ $total }} · {{ $perDay }} / {{ __('messages.day') }}</p>
                @endif

                <p class="text-sm opacity-90">🔥 <b>{{ $h->currentStreakDays() }}</b> {{ __('messages.day') }} — 🏆 {{ $h->bestStreakDays() }} {{ __('messages.day') }}</p>
              </div>

              <span class="badge {{ $h->type === 'good_habit' ? 'badge-success' : 'badge-error' }} capitalize shrink-0">
                {{ __("messages.{$h->type}") }}
              </span>
            </div>

            <div class="mt-4 flex items-center gap-2 flex-wrap justify-between">
              <div class="flex flex-wrap gap-2">
                @if($h->is_active)
                  <button type="button" class="btn btn-outline btn-sm" wire:click="archiveHabit({{ $h->id }})">{{ __('messages.archive') }}</button>
                @else
                  <button type="button" class="btn btn-outline btn-sm" wire:click="restoreHabit({{ $h->id }})">{{ __('messages.restore') }}</button>
                @endif
                <button type="button" class="btn btn-outline btn-sm" wire:click="openCalendar({{ $h->id }})">{{ __('messages.calendar') }}</button>
              </div>

              <div>
                @if($h->currentPeriod())
                  <button type="button" class="btn btn-warning btn-sm" wire:click="stopHabit({{ $h->id }})">{{ __('messages.stop_') }}</button>
                @else
                  <button type="button" class="btn btn-success btn-sm" wire:click="startHabit({{ $h->id }})">{{ __('messages.restart') }}</button>
                @endif
              </div>
            </div>
          </div>
        </div>

        @empty
          <div class="col-span-full">
            <div class="alert bg-rose-600 border-rose-500">
              <span>{{ __('messages.no_habit') }}</span>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    @if($calendarOpen && $calendar)
      @php
        $badge  = $calendar['habit']->type === 'good_habit' ? 'badge-success' : 'badge-error';
      @endphp
    <div class="modal modal-open">
      <div class="modal-box max-w-3xl bg-sky-50">
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
          <span class="inline-block w-4 h-4 rounded bg-error"></span> Non tenu
          <span class="ml-auto badge {{ $badge }}">{{ __("messages.{$calendar['habit']->type}") }}</span>
        </div>

        {{-- En-têtes dynamiques selon locale --}}
        <div class="grid grid-cols-7 gap-1 text-xs font-semibold opacity-70 mb-1">
          @foreach($calendar['weekdayLabels'] as $lbl)
            <div class="text-center">{{ $lbl }}</div>
          @endforeach
        </div>

        {{-- Grille calendrier --}}
        <div class="grid grid-cols-7 gap-1">
          {{-- offsets avant le 1er du mois --}}
          @for($i=0; $i < $calendar['lead']; $i++)
            <div class="h-10 rounded bg-transparent"></div>
          @endfor

          @foreach($calendar['days'] as $d)
            <div class="h-10 rounded grid place-items-center text-sm
                        {{ $d['isStop'] ? 'bg-error text-error-content' : ($d['active'] ? 'bg-success text-success-content' : 'bg-base-300') }}
                        {{ $d['isToday'] ? 'ring-2 ring-primary' : '' }}">
              {{ $d['day'] }}
            </div>
          @endforeach
        </div>

        <div class="modal-action">
          <button class="btn btn-success" wire:click="$set('calendarOpen', false)">{{ __('messages.close') }}</button>
        </div>
      </div>
    </div>
    @endif
</div>
