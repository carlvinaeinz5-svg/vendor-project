<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_requires_authentication(): void
    {
        $this->get(route('settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_settings_and_shared_contact_layout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('vendorcustomer@co.ug')
            ->assertSee('0747 944 559')
            ->assertSee('nav-settings-link', false)
            ->assertSee('aria-label="Settings"', false)
            ->assertSee('data-theme="market"', false);
    }

    public function test_user_can_update_profile_and_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.profile'), [
                'name' => 'Market Customer',
                'email' => 'customer@example.com',
                'phone' => '0747000000',
            ])
            ->assertSessionHasNoErrors();

        $this->put(route('settings.preferences'), [
            'theme' => 'ocean',
            'preferred_payment_method' => 'Mobile Money (pay vendor directly)',
        ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Market Customer',
            'email' => 'customer@example.com',
            'phone' => '0747000000',
            'theme' => 'ocean',
            'preferred_payment_method' => 'Mobile Money (pay vendor directly)',
            'email_notifications' => false,
        ]);
    }

    public function test_customer_or_vendor_can_upload_a_profile_picture(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'vendor']);

        $this->actingAs($user)
            ->put(route('settings.profile'), [
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => UploadedFile::fake()->create('profile.png', 10, 'image/png'),
            ])
            ->assertSessionHasNoErrors();

        $path = $user->fresh()->profile_image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_user_can_change_password_after_entering_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put(route('settings.password'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
