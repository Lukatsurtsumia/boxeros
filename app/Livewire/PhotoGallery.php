<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Photo;

class PhotoGallery extends Component
{
    use WithFileUploads;

    public $photos_upload = [];
    public $type = 'body', $caption, $taken_at;
    public $filterType = 'all';
    public bool $showForm = false;

    public function mount()
    {
        $this->taken_at = today()->toDateString();
    }

    public function save()
    {
        $this->validate([
            'photos_upload.*' => 'required|image|max:5120',
            'type' => 'required|in:profile,body,food,fight,other',
            'caption' => 'nullable|string|max:200',
            'taken_at' => 'nullable|date',
        ]);

        foreach ($this->photos_upload as $photo) {
            $path = $photo->store('photos', 'public');
            auth()->user()->photos()->create([
                'path' => $path,
                'type' => $this->type,
                'caption' => $this->caption,
                'taken_at' => $this->taken_at,
            ]);
        }

        $this->reset(['photos_upload', 'caption']);
        $this->showForm = false;
        session()->flash('message', 'Photos uploaded!');
    }

    public function delete($id)
    {
        $photo = auth()->user()->photos()->findOrFail($id);
        \Storage::disk('public')->delete($photo->path);
        $photo->delete();
    }

    public function render()
    {
        $query = auth()->user()->photos()->orderByDesc('taken_at');
        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }
        $photos = $query->get();

        return view('livewire.photo-gallery', compact('photos'))->layout('layouts.app');
    }
}
