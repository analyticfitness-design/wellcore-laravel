<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            [
                'source' => 'footer',
                'ip_hash' => hash('sha256', $request->ip()),
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Suscrito exitosamente']);
    }
}
