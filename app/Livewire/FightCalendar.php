<?php

namespace App\Livewire;

use Livewire\Component;

class FightCalendar extends Component
{
    public $editingId = null;
    public $opponent_name, $event_name, $venue, $location;
    public $fight_date, $weight_class, $rounds = 3, $result = 'upcoming';
    public $result_method, $notes;
    public bool $showForm = false;

    public function mount()
    {
        $this->fight_date = now()->addMonth()->format('Y-m-d\TH:i');
    }

    public function save()
    {
        $this->validate([
            'opponent_name' => 'required|string|max:100',
            'fight_date' => 'required|date',
            'rounds' => 'integer|min:1|max:15',
            'result' => 'required|in:win,loss,draw,no_contest,upcoming',
        ]);

        $data = [
            'opponent_name' => $this->opponent_name,
            'event_name' => $this->event_name,
            'venue' => $this->venue,
            'location' => $this->location,
            'fight_date' => $this->fight_date,
            'weight_class' => $this->weight_class,
            'rounds' => $this->rounds,
            'result' => $this->result,
            'result_method' => $this->result_method,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            auth()->user()->fights()->findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Fight updated!');
        } else {
            auth()->user()->fights()->create($data);
            session()->flash('message', 'Fight added!');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function edit($id)
    {
        $fight = auth()->user()->fights()->findOrFail($id);
        $this->editingId     = $fight->id;
        $this->opponent_name = $fight->opponent_name;
        $this->event_name    = $fight->event_name;
        $this->venue         = $fight->venue;
        $this->location      = $fight->location;
        $this->fight_date    = $fight->fight_date->format('Y-m-d\TH:i');
        $this->weight_class  = $fight->weight_class;
        $this->rounds        = $fight->rounds;
        $this->result        = $fight->result;
        $this->result_method = $fight->result_method;
        $this->notes         = $fight->notes;
        $this->showForm      = true;
    }

    public function addNew()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function cancelForm()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm()
    {
        $this->reset(['editingId', 'opponent_name', 'event_name', 'venue', 'location', 'weight_class', 'result_method', 'notes']);
        $this->result = 'upcoming';
        $this->rounds = 3;
        $this->fight_date = now()->addMonth()->format('Y-m-d\TH:i');
    }

    public function delete($id)
    {
        auth()->user()->fights()->findOrFail($id)->delete();
        if ($this->editingId == $id) {
            $this->cancelForm();
        }
    }

    public function render()
    {
        $upcoming = auth()->user()->fights()
            ->where('result', 'upcoming')
            ->orderBy('fight_date')
            ->get();

        $history = auth()->user()->fights()
            ->where('result', '!=', 'upcoming')
            ->orderByDesc('fight_date')
            ->get();

        return view('livewire.fight-calendar', compact('upcoming', 'history'))
            ->layout('layouts.app');
    }
}
