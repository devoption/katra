<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class FoundationPreview extends Component
{
    public int $surfaceIndex = 0;

    /**
     * @var array<int, array{label: string, title: string, detail: string}>
     */
    protected array $surfaces = [
        [
            'label' => 'Desktop',
            'title' => 'Desktop-first shell',
            'detail' => 'NativePHP remains the first-class local shell for Katra while Laravel stays at the core.',
        ],
        [
            'label' => 'Server',
            'title' => 'Server deployment',
            'detail' => 'The same Laravel foundation is intended to run as a traditional shared or dedicated server deployment.',
        ],
        [
            'label' => 'Container',
            'title' => 'Container runtime',
            'detail' => 'Docker and Kubernetes targets stay in view so the product model is not trapped in a desktop-only shape.',
        ],
    ];

    public function cycleSurface(): void
    {
        $this->surfaceIndex = ($this->surfaceIndex + 1) % count($this->surfaces);
    }

    public function render(): View
    {
        return view('livewire.foundation-preview', [
            'surfaces' => $this->surfaces,
            'activeSurface' => $this->surfaces[$this->surfaceIndex],
        ]);
    }
}
