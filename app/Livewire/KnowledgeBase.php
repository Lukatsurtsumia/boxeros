<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\KnowledgeEntry;

/**
 * Owner-facing screen to manage CORNER's knowledge base — the coaching material CORNER treats as
 * authoritative. (Single-user app for now; gate this to an admin role before real customers exist.)
 */
class KnowledgeBase extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public function mount(): void
    {
        // Knowledge base is owner/admin only — fighters can't edit CORNER's material.
        abort_unless(auth()->user()?->is_admin, 403);
    }

    public string $title = '';
    public string $content = '';
    public string $keywords = '';
    public bool $is_active = true;

    public function addNew(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $e = KnowledgeEntry::findOrFail($id);
        $this->editingId = $e->id;
        $this->title     = $e->title;
        $this->content   = $e->content;
        $this->keywords  = $e->keywords ?? '';
        $this->is_active = (bool) $e->is_active;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'title'     => 'required|string|max:120',
            'content'   => 'required|string|max:20000',
            'keywords'  => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($this->editingId) {
            KnowledgeEntry::whereKey($this->editingId)->update($data);
        } else {
            KnowledgeEntry::create($data);
        }

        $this->cancelForm();
    }

    public function delete(int $id): void
    {
        KnowledgeEntry::whereKey($id)->delete();
        if ($this->editingId === $id) {
            $this->cancelForm();
        }
    }

    public function toggleActive(int $id): void
    {
        $e = KnowledgeEntry::find($id);
        if ($e) {
            $e->update(['is_active' => ! $e->is_active]);
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset(['title', 'content', 'keywords', 'editingId']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.knowledge-base', [
            'entries' => KnowledgeEntry::orderByDesc('updated_at')->get(),
        ])->layout('layouts.app');
    }
}
