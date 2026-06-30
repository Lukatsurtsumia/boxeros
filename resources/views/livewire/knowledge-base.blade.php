<div class="space-y-4 pb-4">

    <div class="flex items-center justify-between">
        <div>
            <div class="font-display text-2xl font-bold">Teach CORNER</div>
            <div class="text-xs mt-0.5" style="color: var(--text-muted);">Your coaching material — CORNER treats this as authoritative</div>
        </div>
        @if($showForm)
        <button type="button" wire:click="cancelForm" class="btn-ghost text-xs px-3 py-1.5">Cancel</button>
        @else
        <button type="button" wire:click="addNew" class="btn-gold text-xs px-3 py-1.5">+ Add material</button>
        @endif
    </div>

    @if($showForm)
    <form wire:submit="save" class="card space-y-3">
        <div class="section-label">{{ $editingId ? 'Edit material' : 'New material' }}</div>

        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Title</label>
            <input type="text" wire:model="title" class="input-dark" placeholder="e.g. Fight-week weight cut protocol" required>
            @error('title') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Content</label>
            <textarea wire:model="content" rows="9" class="input-dark" placeholder="Write your methodology, protocol, or notes here. Plain text or markdown. The more specific, the better CORNER coaches from it."></textarea>
            @error('content') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="text-xs mb-1 block" style="color: var(--text-muted);">Keywords <span style="opacity:0.6;">(optional)</span></label>
            <input type="text" wire:model="keywords" class="input-dark" placeholder="weight cut, hydration, fight week">
        </div>

        <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="checkbox" wire:model="is_active">
            <span>Active — CORNER uses this</span>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="btn-gold flex-1 py-2.5">{{ $editingId ? 'Update' : 'Save' }}</button>
            <button type="button" wire:click="cancelForm" class="btn-ghost px-5 py-2.5">Cancel</button>
        </div>
    </form>
    @endif

    @if($entries->isEmpty() && !$showForm)
    <div class="card text-center py-10">
        <div class="text-4xl mb-3">📚</div>
        <div class="font-display text-xl font-bold mb-2">No material yet</div>
        <p class="text-sm mb-4 px-4" style="color: var(--text-muted);">
            Add your training methodology, weight-cut protocols, nutrition guides, or coaching philosophy.
            CORNER will ground its answers in your material instead of generic knowledge.
        </p>
        <button type="button" wire:click="addNew" class="btn-gold">Add your first piece</button>
    </div>
    @endif

    @if($entries->isNotEmpty())
    <div class="space-y-2">
        @foreach($entries as $e)
        <div wire:key="kb-{{ $e->id }}" class="card">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-sm">{{ $e->title }}</span>
                        @if($e->is_active)
                        <span class="badge badge-gold">Active</span>
                        @else
                        <span class="badge badge-gray">Off</span>
                        @endif
                    </div>
                    <div class="text-xs mt-1 leading-relaxed" style="color: var(--text-muted); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($e->content), 160) }}
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <button type="button" wire:click="edit({{ $e->id }})" class="text-xs px-2.5 py-1 rounded-lg" style="border: 1px solid rgba(243,156,18,0.3); color: var(--gold); background: rgba(243,156,18,0.08);">Edit</button>
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="toggleActive({{ $e->id }})" class="text-xs" style="color: var(--text-muted);">{{ $e->is_active ? 'Disable' : 'Enable' }}</button>
                        <button type="button" wire:click="delete({{ $e->id }})" wire:confirm="Delete this material?" class="text-xs" style="color: var(--blood);">✕</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
