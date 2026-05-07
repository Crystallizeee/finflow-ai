<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\AI\AIChatAssistantService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AIChatController extends Controller
{
    public function __construct(
        private readonly AIChatAssistantService $chatService
    ) {}

    public function index()
    {
        return Inertia::render('Finance/AIChat');
    }

    public function send(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string',
        ]);

        try {
            $response = $this->chatService->chat(auth()->user(), $request->messages);
            
            return response()->json([
                'success' => true,
                'message' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Maaf, AI saat ini sedang sibuk atau mengalami kendala. Silakan coba lagi.'
            ], 500);
        }
    }
}
