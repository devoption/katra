<?php

use App\Livewire\FoundationPreview;
use Livewire\Livewire;

test('the frontend foundation preview route renders with the branded stack', function () {
    $this->get(route('foundation.preview'))
        ->assertSuccessful()
        ->assertSee('Katra UI Foundation')
        ->assertSee('Livewire 4')
        ->assertSee('Tailwind CSS v4')
        ->assertSee('/foundation-preview')
        ->assertSee('Katra');
});

test('the livewire foundation preview cycles through runtime surfaces', function () {
    Livewire::test(FoundationPreview::class)
        ->assertSee('Desktop-first shell')
        ->call('cycleSurface')
        ->assertSee('Server deployment')
        ->call('cycleSurface')
        ->assertSee('Container runtime')
        ->call('cycleSurface')
        ->assertSee('Desktop-first shell');
});

test('the livewire foundation preview clamps invalid surface indexes', function () {
    Livewire::test(FoundationPreview::class)
        ->set('surfaceIndex', 99)
        ->assertSet('surfaceIndex', 2)
        ->assertSee('Container runtime')
        ->set('surfaceIndex', -4)
        ->assertSet('surfaceIndex', 0)
        ->assertSee('Desktop-first shell');
});
