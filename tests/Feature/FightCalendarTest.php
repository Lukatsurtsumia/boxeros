<?php

namespace Tests\Feature;

use App\Livewire\FightCalendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FightCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fight_can_be_added(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FightCalendar::class)
            ->set('opponent_name', 'Carlos Reyes')
            ->set('fight_date', now()->addMonth()->format('Y-m-d\TH:i'))
            ->set('rounds', 10)
            ->set('result', 'upcoming')
            ->call('save');

        $this->assertSame(1, $user->fights()->count());
        $this->assertSame('Carlos Reyes', $user->fights()->first()->opponent_name);
    }

    public function test_a_fight_result_can_be_edited(): void
    {
        $user = User::factory()->create();
        $fight = $user->fights()->create([
            'opponent_name' => 'Test', 'fight_date' => now()->addWeek(), 'rounds' => 12, 'result' => 'upcoming',
        ]);
        $this->actingAs($user);

        Livewire::test(FightCalendar::class)
            ->call('edit', $fight->id)
            ->set('result', 'win')
            ->set('result_method', 'KO R3')
            ->call('save');

        $this->assertSame('win', $fight->fresh()->result);
        $this->assertSame('KO R3', $fight->fresh()->result_method);
    }

    public function test_a_fight_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $fight = $user->fights()->create(['opponent_name' => 'X', 'fight_date' => now(), 'rounds' => 3, 'result' => 'win']);
        $this->actingAs($user);

        Livewire::test(FightCalendar::class)->call('delete', $fight->id);

        $this->assertSame(0, $user->fights()->count());
    }
}
