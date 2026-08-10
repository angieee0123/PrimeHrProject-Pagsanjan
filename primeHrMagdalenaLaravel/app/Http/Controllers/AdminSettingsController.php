<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\UserNotificationPreference;
use App\Models\UserAiSetting;
use App\Models\SystemAiSetting;
use App\Services\AiChatService;
use App\Services\SystemTheme;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $preference = $user->notificationPreference;
        $aiSetting = $user->aiSetting;
        $isSystemAdmin = $user->hasRole('admin');
        $systemAi = $isSystemAdmin ? SystemAiSetting::current() : null;

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
            'isSystemAdmin' => $isSystemAdmin,
            'systemAiProvider' => $systemAi?->provider,
            'systemAiModel' => $systemAi?->model,
            'systemAiMaskedKey' => $systemAi?->maskedKey(),
            'activeTheme'       => SystemAiSetting::current()->theme ?? 'default',
            'themePalettes'     => SystemTheme::all(SystemAiSetting::current()->custom_theme_primary),
            'customThemeColor'  => SystemAiSetting::current()->custom_theme_primary ?? SystemTheme::DEFAULT_CUSTOM_SEED,
            'themeSecondary'    => SystemAiSetting::current()->theme_secondary,
            'themeAccent'       => SystemAiSetting::current()->theme_accent,
            'themeMuted'        => SystemAiSetting::current()->theme_muted,
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

    public function updatePhoto(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No employee record is linked to this account.'], 404);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
        $path = $request->file('photo')->storeAs('employees/photos', $filename, 'public');
        $employee->update(['photo' => '/storage/' . $path]);

        return response()->json(['success' => true, 'message' => 'Profile photo updated.', 'photo' => $employee->photo]);
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

    // Theme endpoints moved to AppearanceController: a palette is now a
    // per-user preference with an organisation-wide default, so it is no
    // longer an admin-settings-only concern.

    public function updateSystemAiSettings(Request $request)
    {        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Only administrators can manage the system default AI provider.'], 403);
        }

        $data = $request->validate([
            'provider' => ['nullable', Rule::in(UserAiSetting::PROVIDERS)],
            'api_key' => ['nullable', 'string', 'max:500'],
            'model' => ['nullable', 'string', 'max:100'],
        ]);

        $system = SystemAiSetting::current();

        if (empty($data['provider'])) {
            $system->update(['provider' => null, 'api_key' => null, 'model' => null]);
            return response()->json(['success' => true, 'message' => 'System default cleared. The chatbot will fall back to .env if configured there.']);
        }

        $apiKey = $data['api_key'] ?? null;
        if (!$apiKey) {
            if (!$system->api_key || $system->provider !== $data['provider']) {
                return response()->json(['success' => false, 'message' => 'An API key is required to enable this provider.'], 422);
            }
            $apiKey = $system->api_key;
        }

        $system->update([
            'provider' => $data['provider'],
            'api_key' => $apiKey,
            'model' => $data['model'] ?: null,
        ]);

        return response()->json(['success' => true, 'message' => 'System default AI settings saved.']);
    }
}
