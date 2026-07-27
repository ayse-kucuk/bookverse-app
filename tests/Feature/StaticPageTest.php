<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_is_accessible(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee(__('ui.pages.about_title'));
    }

    public function test_privacy_page_is_accessible(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee(__('ui.pages.privacy_title'));
    }

    public function test_footer_links_appear_on_homepage(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('ui.footer.about'))
            ->assertSee(__('ui.footer.privacy'));
    }

    public function test_unknown_route_returns_custom_404_page(): void
    {
        $this->get('/bu-sayfa-yok-xyz')
            ->assertNotFound()
            ->assertSee(__('ui.errors.404_title'));
    }
}
