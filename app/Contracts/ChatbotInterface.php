<?php

namespace App\Contracts;

interface ChatbotInterface
{
    /**
     * Process a message and return a response.
     */
    public function respond(string $message, array $context = []): string;

    /**
     * Get the name of the chatbot provider.
     */
    public function provider(): string;

    /**
     * Check if the chatbot is available/configured.
     */
    public function isAvailable(): bool;
}
