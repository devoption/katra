<?php

use App\Livewire\FoundationPreview;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

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
