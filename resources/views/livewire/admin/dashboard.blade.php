<div class="max-w-7xl mx-auto p-6">

  <h1 class="text-3xl font-bold mb-6 text-center">{{ __('messages.admin_page') }}</h1>

  {{-- Onglets DaisyUI --}}
  <div role="tablist" class="tabs tabs-boxed justify-center mb-6">
    <button wire:click="setTab('users')" role="tab"
            class="tab {{ $tab === 'users' ? 'tab-active' : '' }}">
      {{ __('messages.users') }}
    </button>
    <button wire:click="setTab('habits')" role="tab"
            class="tab {{ $tab === 'habits' ? 'tab-active' : '' }}">
      {{ __('messages.habits') }}
    </button>
    <button wire:click="setTab('periods')" role="tab"
            class="tab {{ $tab === 'periods' ? 'tab-active' : '' }}">
      {{ __('messages.periods') }}
    </button>
  </div>

  {{-- Contenu selon l’onglet --}}
   <div class="card bg-base-100 shadow-lg p-4 sm:p-6"
         x-data="{ show: true }"
         x-transition.opacity.duration.400ms>
     <template x-if="show">
        <div>
          @if($tab === 'users')
            <livewire:admin.users.index key="tab-users" />
          @elseif($tab === 'habits')
            <livewire:admin.habits.index key="tab-habits" />
          @elseif($tab === 'periods')
            <livewire:admin.periods.index key="tab-periods" />
          @endif
        </div>
    </template>
  </div>
</div>
