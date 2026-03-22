<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'session_id' => 'required|string|max:64',
            'page_url' => 'nullable|string|max:500',
        ]);

        // Save user message
        ChatMessage::create([
            'session_id' => $request->session_id,
            'role' => 'user',
            'content' => $request->message,
            'page_url' => $request->page_url,
            'ip_hash' => hash('sha256', $request->ip()),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        // Get bot response
        $response = $chatbot->getResponse($request->message);

        // Save assistant message
        ChatMessage::create([
            'session_id' => $request->session_id,
            'role' => 'assistant',
            'content' => $response,
            'page_url' => $request->page_url,
            'ip_hash' => hash('sha256', $request->ip()),
        ]);

        return response()->json(['message' => $response]);
    }
}
