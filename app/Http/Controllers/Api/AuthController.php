<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        
        Log::info('Register request data: ', $request->all());
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'qiraat' => $request->qiraat,
            ]);
        Log::info('User created: ', ['user_id' => $user->id]);
            // Assign default role
            $user->assignRole('student');

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        Log::info('Login request data: ', $request->only('email'));
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
                ]);
            }

            // Check if user is active
             Log::info('User found: ', ['user_id' => $user->id]);
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حسابك غير مفعل. يرجى التواصل مع الإدارة',
                ], 403);
            }

            Log::info('User is active: ', ['user_id' => $user->id]);
            
            // Revoke all existing tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الخروج',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('roles');

            return response()->json([
                'success' => true,
                'data' => $this->formatUserData($user),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل بيانات المستخدم',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'qiraat' => 'sometimes|nullable|string|max:100',
                'profile_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = $request->user();
            $updateData = $request->only(['name', 'phone', 'qiraat']);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image) {
                    \Storage::disk('public')->delete($user->profile_image);
                }

                $imagePath = $request->file('profile_image')->store('profile_images', 'public');
                $updateData['profile_image'] = $imagePath;
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح',
                'data' => $this->formatUserData($user->fresh()),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الملف الشخصي',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = $request->user();
            Log::info($user);
            Log::info($request);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Revoke all tokens except current
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير كلمة المرور',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم تجديد الرمز المميز بنجاح',
                'data' => [
                    'token' => $token,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تجديد الرمز المميز',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format user data for response.
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'national_id' => $user->national_id,
            'qiraat' => $user->qiraat,
            'profile_image' => $user->profile_image_url,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}

