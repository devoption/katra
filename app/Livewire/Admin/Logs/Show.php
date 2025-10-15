<?php

namespace App\Livewire\Admin\Logs;

use App\Models\AiInteraction;
use App\Models\AiInteractionFeedback;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('AI Interaction Detail - Katra')]
class Show extends Component
{
    public $interactionId;

    // Feedback form
    public ?int $rating = null;

    public ?bool $thumbs_up = null;

    public string $feedback_type = '';

    public string $correction_text = '';

    public string $explanation = '';

    public array $tags = [];

    public float $weight = 1.00;

    public string $notes = '';

    public function mount(AiInteraction $interaction): void
    {
        $this->interactionId = $interaction->id;
    }

    public function getInteractionProperty(): AiInteraction
    {
        return AiInteraction::with(['user', 'agent', 'workflowExecution', 'feedback.user', 'childInteractions'])
            ->findOrFail($this->interactionId);
    }

    public function submitFeedback(): void
    {
        $validated = $this->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'thumbs_up' => ['nullable', 'boolean'],
            'feedback_type' => ['nullable', 'string'],
            'correction_text' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'weight' => ['required', 'numeric', 'min:0', 'max:1'],
            'notes' => ['nullable', 'string'],
        ]);

        AiInteractionFeedback::create([
            'ai_interaction_id' => $this->interactionId,
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Feedback submitted successfully!',
        ]);

        $this->resetFeedbackForm();
    }

    public function verifyFeedback(AiInteractionFeedback $feedback): void
    {
        $feedback->update([
            'verified_by_admin' => true,
            'verified_by' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Feedback verified successfully!',
        ]);
    }

    public function toggleTraining(): void
    {
        $interaction = $this->interaction;
        $interaction->update([
            'include_in_training' => ! $interaction->include_in_training,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $interaction->include_in_training
                ? 'Included in training data.'
                : 'Excluded from training data.',
        ]);
    }

    public function deleteFeedback(AiInteractionFeedback $feedback): void
    {
        $feedback->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Feedback deleted successfully.',
        ]);
    }

    private function resetFeedbackForm(): void
    {
        $this->rating = null;
        $this->thumbs_up = null;
        $this->feedback_type = '';
        $this->correction_text = '';
        $this->explanation = '';
        $this->tags = [];
        $this->weight = 1.00;
        $this->notes = '';
    }

    public function render()
    {
        return view('livewire.admin.logs.show', [
            'interaction' => $this->interaction,
        ]);
    }
}
