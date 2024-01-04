<?php

namespace Laravel\Telescope\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Telescope\Storage\AdminModel;

class SessionController extends Controller
{
    const ROLE_SUPERADMIN = "superadmin";

    public function me(Request $request)
    {
        if (!session()->has('telescope.is_authenticated')) {
            abort(401);
        }

        return response()->json([
            "user" => session()->get('telescope.auth.user')
        ]);
    }

    public function login(Request $request)
    {
        // if (session()->has('telescope.is_authenticated')) {
        //     return response()->noContent();
        // }

        $credentials = $request->only('email', 'password');
        $admin = AdminModel::where('email', $credentials['email'])
            ->first();
        
        if (is_null($admin)) {
            abort(401);
        }

        if (!password_verify($credentials['password'], $admin->password)) {
            abort(401);
        }

        session()->put('telescope.is_authenticated', true);
        session()->put('telescope.auth.user', collect($admin)->except('password'));

        return response()->noContent();
    }

    public function logout(Request $request)
    {
        session()->forget('telescope.is_authenticated');
        session()->forget('telescope.auth.user');

        return response()->noContent();
    }

    public function applications()
    {
        $applications = \Laravel\Telescope\Storage\ApplicationModel::all();

        $user = session()->get('telescope.auth.user');

        if (!$user) {
            abort(401);
        }

        if ($user['role'] != self::ROLE_SUPERADMIN)
        {
            $applications = $applications->whereIn("uuid", $this->allowed_applications);
        }

        return response()->json($applications->pluck('name', 'uuid'));
    }
}