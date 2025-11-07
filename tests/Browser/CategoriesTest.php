<?php

use App\Models\Category;
use App\Models\User;

it('lists categories on index page', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Groceries']);
    Category::factory()->create(['name' => 'Healthcare']);
    Category::factory()->create(['name' => 'Food Delivery']);

    loginAs($user->email)
        ->navigate('/categories')
        ->assertSee('Groceries')
        ->assertSee('Healthcare')
        ->assertSee('Food Delivery');
});

it('shows validation errors on create category', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/categories/create')
        ->press('Create Category')
        ->assertSee('The name field is required.');
});

it('can create a new category', function () {
    $user = User::factory()->create();

    loginAs($user->email)
        ->navigate('/categories')
        ->press('Add Category')
        ->fill('name', 'Utilities')
        ->press('Create Category')
        ->assertSee('Category created successfully.')
        ->assertSee('Utilities');
});

it('can edit a category', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Old Name']);

    loginAs($user->email)
        ->navigate('/categories')
        ->click('Old Name')
        ->fill('name', 'New Name')
        ->press('Update')
        ->assertSee('Category updated successfully.')
        ->assertSee('New Name');
});

it('shows validation errors on edit category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    loginAs($user->email)
        ->navigate("/categories/$category->id/edit")
        ->fill('name', '')
        ->press('Update')
        ->assertSee('The name field is required.');
});
