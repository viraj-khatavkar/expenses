<?php

use App\Models\User;

it('shows the change password page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/account/password')
        ->assertOk();
});

it('shows validation errors for change password', function (array $data, array $invalid, array $valid) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/account/password', $data)
        ->assertInvalid($invalid)
        ->assertValid($valid);
})->with([
    'empty fields' => [[], ['current_password', 'password'], []],
    'wrong current password' => [
        ['current_password' => 'wrong-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'],
        ['current_password'],
        ['password'],
    ],
    'password too short' => [
        ['current_password' => 'password', 'password' => 'short', 'password_confirmation' => 'short'],
        ['password'],
        ['current_password'],
    ],
    'password confirmation mismatch' => [
        ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'different'],
        ['password'],
        ['current_password'],
    ],
]);

it('can change password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/account/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect('/account/password')
        ->assertSessionHas('success', 'Password changed successfully.');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
