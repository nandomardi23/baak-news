<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated admin users can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    // /dashboard redirects to /admin, so follow the redirect
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('admin.dashboard'));

    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(200);
});