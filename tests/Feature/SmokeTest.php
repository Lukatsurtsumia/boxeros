<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_the_app(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/log')->assertRedirect('/login');
        $this->get('/fights')->assertRedirect('/login');
    }

    public function test_all_authenticated_pages_render(): void
    {
        $user = User::factory()->create();
        // Onboarded fighter (a profile-less user is sent through onboarding).
        $user->boxerProfile()->create(['wins' => 0, 'losses' => 0, 'draws' => 0, 'stance' => 'orthodox']);

        foreach (['/dashboard', '/boxer/profile', '/log', '/meals', '/fights', '/chat', '/plan'] as $route) {
            $this->actingAs($user)->get($route)->assertOk();
        }
    }

    public function test_new_user_without_profile_is_sent_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('onboarding'));
        $this->actingAs($user)->get('/welcome')->assertOk();
    }

    public function test_knowledge_base_is_admin_only(): void
    {
        $fighter = User::factory()->create(['is_admin' => false]);
        $fighter->boxerProfile()->create(['wins' => 0, 'losses' => 0, 'draws' => 0, 'stance' => 'orthodox']);
        $this->actingAs($fighter)->get('/knowledge')->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);
        $admin->boxerProfile()->create(['wins' => 0, 'losses' => 0, 'draws' => 0, 'stance' => 'orthodox']);
        $this->actingAs($admin)->get('/knowledge')->assertOk();
    }

    public function test_landing_page_renders_for_guests(): void
    {
        $this->get('/')->assertOk()->assertSee('BOXER');
    }
}
