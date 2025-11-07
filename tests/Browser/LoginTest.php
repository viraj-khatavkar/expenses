<?php

use App\Models\User;

it('shows validation errors', function () {
    $page = visit('/login')
        ->fill('email', '')
        ->fill('password', '')
        ->press('Sign in')
        ->assertSee('The email field is required.')
        ->assertSee('The password field is required.');
});

it('shows correct message for invalid credentials', function () {
    $page = visit('/login')
        ->fill('email', 'abc@example.com')
        ->fill('password', 'secret@123')
        ->press('Sign in')
        ->assertSee('The provided credentials do not match our records.');
});

it('redirects to home page post correct login', function () {
    $user = User::factory()->create();

    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->press('Sign in')
        ->assertPathIs('/');
});
