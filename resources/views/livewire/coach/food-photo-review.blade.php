<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl tracking-wide text-wc-text">FOTOS DE COMIDA</h1>
            <p class="text-sm text-wc-text-secondary">
                {{ $pendingCount }} pendiente{{ $pendingCount === 1 ? '' : 's' }} de revisión
            </p>
        </div>

        <div class="flex items-center gap-2">
            <select wire:model.live="selectedClientId"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text">
                <option :value="null">Todos los clientes</option>
                @foreach ($allClients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <button wire:click="toggleFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text transition hover:bg-wc-bg-tertiary">
                {{ $showReviewed ? 'Ver Pendientes' : 'Ver Revisadas' }}
            </button>
        </div>
    </div>

    @if ($photos->isEmpty())
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-10 text-center text-wc-text-secondary">
            {{ $showReviewed ? 'No has revisado fotos aún.' : 'Sin fotos pendientes.' }}
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($photos as $photo)
                @php $client = $clientsById->get($photo->client_id); @endphp
                <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                    <div class="flex items-center gap-3 border-b border-wc-border p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10 text-sm font-bold text-wc-accent">
                            {{ substr($client->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-wc-text">{{ $client->name ?? 'Cliente' }}</p>
                            <p class="text-xs text-wc-text-tertiary">
                                {{ $photo->meal_name }} ·
                                {{ \Carbon\Carbon::parse($photo->photo_date)->format('d M') }} ·
                                {{ $photo->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <img src="{{ $photo->photo_url }}" alt="Foto de {{ $photo->meal_name }}"
                         class="h-64 w-full object-cover">

                    <div class="space-y-3 p-4">
                        @if ($photo->coach_seen)
                            <div class="flex items-center gap-2 text-sm">
                                @if ($photo->coach_reaction === 'bien')
                                    <span class="rounded-full bg-green-500/10 px-2 py-0.5 text-green-400">✅ Bien</span>
                                @elseif ($photo->coach_reaction === 'mejorar')
                                    <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-amber-400">⚠️ Por mejorar</span>
                                @else
                                    <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5">Vista sin reacción</span>
                                @endif
                            </div>
                        @else
                            <div class="flex gap-2">
                                <button wire:click="react({{ $photo->id }}, 'bien')"
                                        class="flex-1 rounded-lg border border-green-500/30 bg-green-500/10 py-2 text-sm font-semibold text-green-400 transition hover:bg-green-500/20">
                                    ✅ Bien
                                </button>
                                <button wire:click="react({{ $photo->id }}, 'mejorar')"
                                        class="flex-1 rounded-lg border border-amber-500/30 bg-amber-500/10 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/20">
                                    ⚠️ Mejorar
                                </button>
                            </div>
                        @endif

                        <textarea wire:model="noteMap.{{ $photo->id }}"
                                  wire:change="saveNote({{ $photo->id }})"
                                  rows="2"
                                  placeholder="Nota opcional para el cliente"
                                  class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text">{{ $photo->coach_note }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $photos->links() }}</div>
    @endif
</div>
