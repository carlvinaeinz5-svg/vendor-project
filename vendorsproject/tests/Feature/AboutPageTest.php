<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_explains_the_project_motivation_goals_and_founders(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('research work')
            ->assertSee('Climax')
            ->assertSee('BBC students at MUBS')
            ->assertSee('Give vendors more visibility')
            ->assertSee('Make local goods easier to find')
            ->assertSee('About Nakawa Market');
    }
}
