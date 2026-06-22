<div class="space-y-4 pb-4">

    <div class="flex items-center justify-between">
        <div class="font-display text-2xl font-bold">Gallery</div>
        <button wire:click="$toggle('showForm')" class="{{ $showForm ? 'btn-ghost' : 'btn-primary' }} text-xs px-3 py-1.5">
            {{ $showForm ? 'Cancel' : '+ Upload' }}
        </button>
    </div>

    {{-- Upload form --}}
    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Photos</label>
            <input type="file" wire:model="photos_upload" class="input-dark text-xs" accept="image/*" multiple>
            @error('photos_upload.*') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Category</label>
                <select wire:model="type" class="input-dark">
                    <option value="body">Body Check</option>
                    <option value="food">Food</option>
                    <option value="fight">Fight</option>
                    <option value="profile">Profile</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs mb-1 block" style="color: var(--text-muted);">Date</label>
                <input type="date" wire:model="taken_at" class="input-dark">
            </div>
        </div>
        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Caption</label>
            <input type="text" wire:model="caption" class="input-dark" placeholder="Optional caption...">
        </div>
        <button type="submit" class="btn-primary w-full py-2.5">
            <span wire:loading.remove>Upload Photos</span>
            <span wire:loading>Uploading...</span>
        </button>
    </form>
    @endif

    {{-- Filter tabs --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach(['all' => 'All', 'body' => 'Body', 'food' => 'Food', 'fight' => 'Fight', 'profile' => 'Profile'] as $val => $label)
        <button wire:click="$set('filterType', '{{ $val }}')"
                class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-all"
                style="{{ $filterType === $val ? 'background: var(--blood); color: white;' : 'background: rgba(255,255,255,0.06); color: var(--text-muted);' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Photo grid --}}
    @if($photos->count() > 0)
    <div class="grid grid-cols-3 gap-2">
        @foreach($photos as $photo)
        <div class="relative group aspect-square">
            <img src="{{ Storage::url($photo->path) }}" class="w-full h-full object-cover rounded-xl">
            <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-end"
                 style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                <div class="p-2 w-full">
                    @if($photo->caption)
                    <div class="text-xs text-white truncate">{{ $photo->caption }}</div>
                    @endif
                    <button wire:click="delete({{ $photo->id }})" class="text-xs" style="color: var(--blood);">Delete</button>
                </div>
            </div>
            <div class="absolute top-1.5 right-1.5">
                @php $typeColors = ['body'=>'badge-blue','food'=>'badge-gold','fight'=>'badge-red','profile'=>'badge-green','other'=>'badge-gray']; @endphp
                <span class="badge {{ $typeColors[$photo->type] ?? 'badge-gray' }}">{{ $photo->type }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card text-center py-10">
        <div class="text-4xl mb-3">📸</div>
        <div class="font-display text-xl font-bold mb-2">No Photos Yet</div>
        <p class="text-sm mb-4" style="color: var(--text-muted);">Track your body transformation, meals, and fights.</p>
        <button wire:click="$set('showForm', true)" class="btn-primary">Upload First Photo</button>
    </div>
    @endif

</div>
