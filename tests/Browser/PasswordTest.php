<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('shows the change password page', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/account/password')
        ->assertSee('Change Password');
});

it('shows validation errors when fields are empty', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/account/password')
        ->press('Update Password')
        ->assertSee('The current password field is required')
        ->assertSee('The password field is required');
});

it('rejects an incorrect current password', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/account/password')
        ->type('current_password', 'wrong-password')
        ->type('password', 'new-password')
        ->type('password_confirmation', 'new-password')
        ->press('Update Password')
        ->assertSee('The password is incorrect');
});

it('rejects a password that is too short', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/account/password')
        ->type('current_password', 'password')
        ->type('password', 'short')
        ->type('password_confirmation', 'short')
        ->press('Update Password')
        ->assertSee('The password field must be at least 8 characters');
});

it('rejects mismatched password confirmation', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/account/password')
        ->type('current_password', 'password')
        ->type('password', 'new-password')
        ->type('password_confirmation', 'different-password')
        ->press('Update Password')
        ->assertSee('The password field confirmation does not match');
});

it('changes the password when all inputs are valid', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/account/password')
        ->type('current_password', 'password')
        ->type('password', 'new-password')
        ->type('password_confirmation', 'new-password')
        ->press('Update Password')
        ->assertSee('Password changed successfully.');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
