<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FrontendPageEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_models_include_models_that_cannot_form_a_voting_pair(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'SAAB',
            Car::FIELD_MODEL => '900',
        ]);
        CarPhoto::factory()->for($car)->create();

        $this->getJson('/voting/models')
            ->assertOk()
            ->assertJsonMissing(['key' => 'SAAB 900']);

        $this->getJson('/statistics/models')
            ->assertOk()
            ->assertJsonFragment([
                'key'   => 'SAAB 900',
                'label' => 'SAAB 900',
            ]);
    }

    public function test_voting_and_statistics_pages_render_their_separate_mount_points(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="voting-page"', false);

        $this->get('/statistics')
            ->assertOk()
            ->assertSee('id="statistics-page"', false)
            ->assertSee('data-statistics-api="/statistics/data"', false);
    }

    public function test_voting_page_renders_controls_for_each_photo_choice(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="voting-model"', false)
            ->assertSee('data-side="left"', false)
            ->assertSee('data-side="right"', false)
            ->assertSee('name="csrf-token"', false);
    }
}
