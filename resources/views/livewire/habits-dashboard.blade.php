<div class="max-w-6xl mx-auto p-6 space-y-6">

  {{-- Header + formulaire pour ajouter une habitude --}}
  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold">Happy — Mes habitudes</h1>
      <p class="opacity-70 text-sm">Affiche et gère tes habitudes facilement.</p>
    </div>

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

  {{-- 👇 Inclusion de la vue Orbit (ton fichier orbit.blade.php) --}}
  @include('habits.partials.orbit', ['habits' => $habits])

</div>
