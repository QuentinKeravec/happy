<div class="max-w-5xl mx-auto px-4 py-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">{{ __('messages.periods') }}</h1>
    <div class="flex gap-2">
      <input type="text" class="input input-bordered input-sm" placeholder="{{ __('messages.search') }}" wire:model.live.debounce.300ms="q">
      <button class="btn btn-primary btn-sm" wire:click="openCreate">+ {{ __('messages.add') }}</button>
    </div>
  </div>

  @if(session('ok'))
    <div class="alert alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <div class="overflow-x-auto">
    <table class="table table-sm">
      <thead>
        <tr>
          <th>ID</th><th>{{ __('messages.habit_label') }}</th><th>{{ __('messages.begin_date') }}</th><th>{{ __('messages.end_date') }}</th><th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($periods as $p)
        <tr>
          <td>{{ $p->id }}</td>
          <td>{{ $p->habit->name ?? '—' }}</td>
          <td>{{ $p->started_at?->format('Y-m-d') }}</td>
          <td>{{ $p->ended_at?->format('Y-m-d') ?? '—' }}</td>
          <td class="text-right">
            <button class="btn btn-xs" wire:click="openEdit({{ $p->id }})">{{ __('messages.edit') }}</button>
            <button class="btn btn-xs btn-ghost" wire:click="delete({{ $p->id }})">{{ __('messages.delete') }}</button>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $periods->links() }}</div>

  <dialog class="modal" @if($modalOpen) open @endif>
    <div class="modal-box">
      <h3 class="font-bold text-lg">{{ __('messages.period_action') }} {{ $editId ? __('messages.edit') : __('messages.add') }}</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="label-text">{{ __('messages.habit_label') }}</label>
          <select class="select select-bordered w-full" wire:model="habit_id">
            <option value="">—</option>
            @foreach($habits as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
          @error('habit_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label-text">{{ __('messages.begin_date') }}</label>
          <input type="date" class="input input-bordered w-full" wire:model="started_at">
          @error('started_at')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label-text">{{ __('messages.end_date') }}</label>
          <input type="date" class="input input-bordered w-full" wire:model="ended_at">
          @error('ended_at')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="modal-action">
        <button class="btn" wire:click="$set('modalOpen', false)">{{ __('messages.cancel') }}</button>
        <button class="btn btn-primary" wire:click="save">{{ __('messages.edit') }}</button>
      </div>
    </div>
  </dialog>
</div>
