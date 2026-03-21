<?php

namespace App\Services;

use App\Contracts\ChatbotInterface;
use App\Services\Chatbot\AIChatbot;
use App\Services\Chatbot\KeywordChatbot;

class ChatbotService
{
    private ChatbotInterface $chatbot;

    public function __construct()
    {
        $ai = new AIChatbot();
        $this->chatbot = $ai->isAvailable() ? $ai : new KeywordChatbot();
    }

    /**
     * Process a message and return a response.
     * Kept as getResponse() to maintain backward compatibility with ChatController.
     */
    public function getResponse(string $message, array $context = []): string
    {
        return $this->chatbot->respond($message, $context);
    }

    public function respond(string $message, array $context = []): string
    {
        return $this->chatbot->respond($message, $context);
    }

    public function provider(): string
    {
        return $this->chatbot->provider();
    }
}
