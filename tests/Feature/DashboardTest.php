<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected based on role', function () {
    // Create a user with the 'user' role
    $user = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(), // Ensure email is verified
    ]);

    $this->actingAs($user);

    // Expect redirection to user dashboard
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('user.dashboard'));

    // Now check if the user dashboard page loads successfully
    $dashboardResponse = $this->get(route('user.dashboard'));
    $dashboardResponse->assertStatus(200);
});

test('admin users are redirected to profile edit', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin);

    // Expect redirection to profile.edit
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('profile.edit'));
});
