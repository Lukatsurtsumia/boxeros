<?php

namespace App\Livewire;

use Livewire\Component;

class FightCalendar extends Component
{
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

        auth()->user()->fights()->create([
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
        ]);

        $this->reset(['opponent_name', 'event_name', 'venue', 'location', 'result_method', 'notes']);
        $this->showForm = false;
        session()->flash('message', 'Fight added!');
    }

    public function delete($id)
    {
        auth()->user()->fights()->findOrFail($id)->delete();
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
