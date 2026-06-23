<?php

namespace Tests\Feature;

use App\Livewire\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeightTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_logging_a_weighin_creates_an_entry_and_sets_current_weight(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DailyLog::class)
            ->set('weighInValue', '74.2')
            ->set('weighInContext', 'morning')
            ->call('saveWeighIn');

        $this->assertSame(1, $user->weightEntries()->count());
        $this->assertSame(74.2, $user->fresh()->currentWeight());
    }

    public function test_blank_weighin_is_a_noop(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DailyLog::class)->set('weighInValue', '')->call('saveWeighIn');

        $this->assertSame(0, $user->weightEntries()->count());
    }

    public function test_out_of_range_weight_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DailyLog::class)
            ->set('weighInValue', '5')      // below the 30kg floor
            ->call('saveWeighIn')
            ->assertHasErrors('weighInValue');

        $this->assertSame(0, $user->weightEntries()->count());
    }

    public function test_a_weighin_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $entry = $user->weightEntries()->create(['weight_kg' => 74, 'context' => 'morning', 'weighed_at' => now()]);
        $this->actingAs($user);

        Livewire::test(DailyLog::class)->call('deleteWeighIn', $entry->id);

        $this->assertSame(0, $user->weightEntries()->count());
    }
}
