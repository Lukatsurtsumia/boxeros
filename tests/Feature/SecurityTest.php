<?php

namespace Tests\Feature;

use App\Livewire\DailyLog;
use App\Livewire\FightCalendar;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_cannot_delete_another_users_fight(): void
    {
        $owner   = User::factory()->create();
        $attacker = User::factory()->create();
        $fight = $owner->fights()->create(['opponent_name' => 'X', 'fight_date' => now(), 'rounds' => 3, 'result' => 'win']);

        $this->actingAs($attacker);
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(FightCalendar::class)->call('delete', $fight->id);
    }

    public function test_attackers_failed_delete_leaves_the_fight_intact(): void
    {
        $owner   = User::factory()->create();
        $attacker = User::factory()->create();
        $fight = $owner->fights()->create(['opponent_name' => 'X', 'fight_date' => now(), 'rounds' => 3, 'result' => 'win']);

        $this->actingAs($attacker);
        try {
            Livewire::test(FightCalendar::class)->call('delete', $fight->id);
        } catch (ModelNotFoundException) {
            // expected — id not found within the attacker's own fights
        }

        $this->assertDatabaseHas('fights', ['id' => $fight->id]);
    }

    public function test_a_user_cannot_delete_another_users_weighin(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $entry = $owner->weightEntries()->create(['weight_kg' => 74, 'context' => 'morning', 'weighed_at' => now()]);

        $this->actingAs($attacker);
        Livewire::test(DailyLog::class)->call('deleteWeighIn', $entry->id);

        // Scoped query simply matches nothing — the owner's entry survives.
        $this->assertDatabaseHas('weight_entries', ['id' => $entry->id]);
    }
}
