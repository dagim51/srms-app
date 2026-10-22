<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AccommodationTest extends DuskTestCase
{
    /**
     * Test the accommodations index page.
     */
    public function test_accommodation_index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/accommodations')
                ->assertSee('Your Accommodations')
                ->assertSee('Set up new Accommodation');
        });
    }

    /**
     * Test navigating to the create accommodation page.
     */
    public function test_navigate_to_create_accommodation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard/accommodations')
                ->clickLink('Set up new Accommodation')
                ->assertPathIs('/dashboard/accommodations/create')
                ->assertSee('Setup New Accommodation')
                ->assertSee('Select Property');
        });
    }
}
