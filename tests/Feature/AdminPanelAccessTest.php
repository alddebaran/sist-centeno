<?php

use App\Models\User;

beforeEach(function () {
    app()->detectEnvironment(fn () => 'production');
});

test('guests are redirected to the admin login page', function () {
    $response = $this->get('/admin');
    $response->assertRedirect(route('filament.admin.auth.login'));
});

test('authenticated users can access the admin panel outside local', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertOk();
});
