<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmployeeChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileChatbotController extends Controller
{
    public function __construct(
        private readonly EmployeeChatbotService $chatbot
    ) {
    }

    public function chat(Request $request)
    {
        $message = trim((string) $request->input('message', ''));

        if ($message === '') {
            return response()->json([
                'success' => false,
                'message' => 'No message provided',
            ], 400);
        }

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        try {
            $result = $this->chatbot->handle($employee, $message);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Mobile chatbot error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sorry, I could not process your request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
