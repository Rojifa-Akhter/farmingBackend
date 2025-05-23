<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyOTP;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //get user profile
    public function profile()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 422);
        }

        return response()->json(['status' => true, 'data' => $user]);
    }
    //signup or registration
    public function signup(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name'                 => 'required|string|max:255',
            'email'                => 'required|string|email|unique:users,email',
            'address'              => 'required|string|max:255',
            'phone'                => 'nullable|string|max:15',
            'password'             => 'required|string|min:6',
            'role'                 => 'nullable|string|in:super_admin,investor,user',
            'image'                => 'nullable|image',
            'is_verified_investor' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        $new_name = null;
        if ($request->has('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $path      = $image->move(public_path('uploads/profile_images'), $new_name);
        }

        $otp            = rand(100000, 999999);
        $otp_expires_at = now()->addMinutes(10);

        // $role = 'user'; // Default role is 'user'
        $role = 'farmer'; // Default role is 'user'

        // If investor is verified, set role to 'investor'
        if ($request->is_verified_investor) {
            $role = 'investor';
        }

        $user = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'address'              => $request->address,
            'phone'                => $request->phone,
            'password'             => Hash::make($request->password),
            'role'                 => $role,     // Set 'investor' if verified, else 'user'
            'image'                => $new_name, // Can now safely be null
            'otp'                  => $otp,
            'otp_expires_at'       => $otp_expires_at,
            'status'               => 'inactive',
            'is_verified_investor' => $request->is_verified_investor ?? false,
        ]);

        try {
            Mail::to($user->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        $message = match ($user->role) {
            'super_admin' => 'Welcome Super Admin! Please verify your email.',
            'investor' => 'Welcome Investor! Please verify your email.',
            'farmer' => 'Welcome Farmer! Please verify your email.',
            // 'user' => 'Welcome User! Please verify your email.',
            default => 'Welcome! Please verify your email.',
        };

        $token = JWTAuth::fromUser($user);
        return response()->json([
            'status'       => true,
            'message'      => $message,
            'access_token' => $token,
        ], 200);
    }

    // verify email
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }
        $user = User::where('otp', $request->otp)->first();

        if ($user) {
            $user->otp               = null;
            $user->email_verified_at = now();
            $user->status            = 'active';
            $user->save();

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status'       => true,
                'message'      => 'OTP verified successfully.',
                'access_token' => $token,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'error'  => 'Invalid OTP.'], 401);
    }
    public function guard()
    {
        return Auth::guard('api');
    }
    //login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Email or password is incorrect.'], 403);
        }

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['status' => false, 'message' => 'Email or password is incorrect.'], 401);
        }

        return response()->json([
            'status'           => true,
            'message'          => 'Login Successfully',
            'access_token'     => $token,
            'token_type'       => 'bearer',
            'user_information' => $user,
        ], 200);

    }
    //social login
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            $socialMatch = ($request->has('google_id') && $existingUser->google_id === $request->google_id);

            if ($socialMatch) {
                $token = JWTAuth::fromUser($existingUser);
                return response()->json(['access_token' => $token, 'token_type' => 'bearer']);
            } elseif (is_null($existingUser->google_id) && is_null($existingUser->facebook_id)) {
                return response()->json(['message' => 'User already exists. Sign in manually.'], 422);
            } else {
                $existingUser->update([
                    'google_id' => $request->google_id ?? $existingUser->google_id,
                ]);
                $token = JWTAuth::fromUser($existingUser);
                return response()->json(['access_token' => $token, 'token_type' => 'bearer']);
            }
        }

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make(Str::random(16)),
            'role'         => 'farmer',
            'google_id'    => $request->google_id ?? null,
            'address'      => $request->address ?? null,
            'verify_email' => true,
            'status'       => 'active',
        ]);

        // return $user;
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'data'         => $user,

        ]);
    }

    // update profile
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'nullable|string|max:255',
            'address'  => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:16',
            'password' => 'nullable|string|min:6|confirmed',
            'image'    => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $user->name    = $validatedData['name'] ?? $user->name;
        $user->address = $validatedData['address'] ?? $user->address;
        $user->phone   = $validatedData['phone'] ?? $user->phone;

        if (! empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            $existingImage = $user->image;

            if ($existingImage) {
                $oldImage = parse_url($existingImage);
                $filePath = ltrim($oldImage['path'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the existing image
                }
            }

            // Upload new image
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $newName   = time() . '.' . $extension;
            $image->move(public_path('uploads/profile_images'), $newName);

            $user['image'] = $newName;
        }

        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
            'data'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'address' => $user->address,
                'contact' => $user->phone,
                'image'   => $user->image,
                'role'    => $user->role,
            ],
        ], 200);

    }

    //change password
    public function changePassword(Request $request)
    {

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect.'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully']);
    }
    // forgote password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email not registered.'], 401);
        }
        $otp = rand(100000, 999999);

        DB::table('users')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to send OTP.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent to your email.'], 200);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        // return $request;
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found.'], 401);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successful.'], 200);
    }

    //resend otp
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email not registered.'], 401);
        }

        $otp = rand(100000, 999999);

        DB::table('users')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to resend OTP.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP resent to your email.'], 200);
    }
    //logout
    public function logout()
    {
        if (! auth('api')->check()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User is not authenticated.',
            ], 401);
        }

        auth('api')->logout();

        return response()->json([
            'status'  => true,
            'message' => 'Successfully logged out.',
        ]);
    }

}
