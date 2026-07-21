<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\UserNotificationPreference;
use App\Models\UserAiSetting;
use App\Services\AiChatService;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $preference = $user->notificationPreference;
        $aiSetting = $user->aiSetting;

        $contactNumber = $employee ? optional($employee->contacts->firstWhere('type', 'mobile'))->number : null;

        return view('admin.settings.adminSettings', [
            'user' => $user,
            'employee' => $employee,
            'contactNumber' => $contactNumber,
            'prefs' => [
                'leave_requests' => $preference?->leave_requests ?? true,
                'training_submissions' => $preference?->training_submissions ?? true,
                'travel_orders' => $preference?->travel_orders ?? true,
                'employee_requests' => $preference?->employee_requests ?? true,
            ],
            'aiProvider' => $aiSetting?->provider,
            'aiModel' => $aiSetting?->model,
            'aiMaskedKey' => $aiSetting?->maskedKey(),
            'aiDefaultModels' => [
                'groq' => AiChatService::defaultModel('groq'),
                'openai' => AiChatService::defaultModel('openai'),
                'anthropic' => AiChatService::defaultModel('anthropic'),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update(['email' => $data['email']]);

        $employee = $user->employee;
        if ($employee && $request->filled('contact_number')) {
            $mobile = $employee->contacts->firstWhere('type', 'mobile');
            if ($mobile) {
                $mobile->update(['number' => $data['contact_number']]);
            } else {
                $employee->contacts()->create(['type' => 'mobile', 'number' => $data['contact_number']]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->input('new_password'))]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'leave_requests' => ['required', 'boolean'],
            'training_submissions' => ['required', 'boolean'],
            'travel_orders' => ['required', 'boolean'],
            'employee_requests' => ['required', 'boolean'],
        ]);

        UserNotificationPreference::updateOrCreate(['user_id' => $user->id], $data);

        return response()->json(['success' => true, 'message' => 'Notification preferences saved.']);
    }

    public function updateAiSettings(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'provider' => ['nullable', Rule::in(UserAiSetting::PROVIDERS)],
            'api_key' => ['nullable', 'string', 'max:500'],
            'model' => ['nullable', 'string', 'max:100'],
        ]);

        $existing = $user->aiSetting;

        // Empty provider = revert to the system default.
        if (empty($data['provider'])) {
            $existing?->delete();
            return response()->json(['success' => true, 'message' => 'Reverted to the system default AI provider.']);
        }

        $apiKey = $data['api_key'] ?? null;
        if (!$apiKey) {
            if (!$existing || !$existing->api_key || $existing->provider !== $data['provider']) {
                return response()->json(['success' => false, 'message' => 'An API key is required to enable this provider.'], 422);
            }
            $apiKey = $existing->api_key;
        }

        UserAiSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'provider' => $data['provider'],
                'api_key' => $apiKey,
                'model' => $data['model'] ?: null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'AI provider settings saved.']);
    }
}
