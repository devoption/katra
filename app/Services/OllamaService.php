<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.base_url', 'http://localhost:11434');
    }

    /**
     * Check if Ollama is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/tags");

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Ollama is not available', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get all available models from Ollama
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            if (! isset($data['models']) || ! is_array($data['models'])) {
                return [];
            }

            return collect($data['models'])
                ->map(function ($model) {
                    return [
                        'name' => $model['name'] ?? 'unknown',
                        'modified_at' => $model['modified_at'] ?? null,
                        'size' => $model['size'] ?? 0,
                        'digest' => $model['digest'] ?? null,
                        'details' => $model['details'] ?? [],
                    ];
                })
                ->sortBy('name')
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to fetch Ollama models', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get just the model names (simplified)
     */
    public function getModelNames(): array
    {
        $models = $this->getAvailableModels();

        return collect($models)->pluck('name')->toArray();
    }

    /**
     * Check if a specific model exists
     */
    public function modelExists(string $modelName): bool
    {
        $models = $this->getModelNames();

        return in_array($modelName, $models);
    }

    /**
     * Get model details
     */
    public function getModelDetails(string $modelName): ?array
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/api/show", [
                'name' => $modelName,
            ]);

            if (! $response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch model details', [
                'model' => $modelName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate a completion (for future use)
     */
    public function generate(string $model, string $prompt, array $options = []): ?array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => $options,
            ]);

            if (! $response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to generate completion', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Chat completion (for future use)
     */
    public function chat(string $model, array $messages, array $options = []): ?array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/chat", [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
                'options' => $options,
            ]);

            if (! $response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to chat', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
