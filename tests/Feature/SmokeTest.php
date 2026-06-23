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

        foreach (['/dashboard', '/boxer/profile', '/log', '/meals', '/injuries', '/fights', '/chat'] as $route) {
            $this->actingAs($user)->get($route)->assertOk();
        }
    }

    public function test_landing_page_renders_for_guests(): void
    {
        $this->get('/')->assertOk()->assertSee('BOXER');
    }
}
