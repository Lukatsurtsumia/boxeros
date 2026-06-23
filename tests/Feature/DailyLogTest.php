<?php

namespace Tests\Feature;

use App\Livewire\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DailyLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_mood_autosaves_on_change(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DailyLog::class)->set('mood', 'great');

        $this->assertDatabaseHas('daily_logs', [
            'user_id' => $user->id,
            'mood'    => 'great',
        ]);
    }

    public function test_water_quick_add_persists_instantly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DailyLog::class)->call('addWater', 0.5)->call('addWater', 0.25);

        $this->assertEquals(0.75, $user->dailyLogs()->whereDate('log_date', today())->value('water_liters'));
    }

    public function test_multiple_training_sessions_sum_into_total_minutes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DailyLog::class)
            ->call('addSession')
            ->set('sessions.0.type', 'boxing')
            ->set('sessions.0.minutes', 60)
            ->call('addSession')
            ->set('sessions.1.type', 'cycling')
            ->set('sessions.1.minutes', 45);

        $log = $user->dailyLogs()->whereDate('log_date', today())->first();
        $this->assertSame(105, (int) $log->training_minutes);   // derived total
        $this->assertCount(2, $log->sessions);
        $this->assertSame('boxing', $log->training_type);       // first session
    }

    public function test_a_past_day_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->dailyLogs()->create(['log_date' => today()->subDay(), 'mood' => 'good', 'energy_level' => 5, 'water_liters' => 1]);
        $this->actingAs($user);

        Livewire::test(DailyLog::class)->call('deleteLog', today()->subDay()->toDateString());

        $this->assertSame(0, $user->dailyLogs()->count());
    }
}
