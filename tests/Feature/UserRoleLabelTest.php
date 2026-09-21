<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_label_accessor_maps_each_role_to_spanish(): void
    {
        $this->assertEquals('Administrador', User::factory()->admin()->make()->role_label);
        $this->assertEquals('Director', User::factory()->director()->make()->role_label);
        $this->assertEquals('Trabajador', User::factory()->worker()->make()->role_label);
    }

    public function test_team_list_shows_director_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $director = User::factory()->director()->create(['name' => 'Carlos Torres']);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $response->assertSeeInOrder(['Carlos Torres', 'Director']);
        $response->assertDontSee('Trabajador');
    }

    public function test_edit_form_has_director_option_preselected_for_a_director(): void
    {
        $admin = User::factory()->admin()->create();
        $director = User::factory()->director()->create();

        $response = $this->actingAs($admin)->get("/users/{$director->id}/edit");

        $response->assertOk();
        $response->assertSee('value="director"', false);
        $response->assertSee('value="director" selected', false);
    }

    public function test_header_shows_role_label_for_logged_in_director(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/orders');

        $response->assertOk();
        $response->assertSee('Director');
    }
}
