<?php
//app/Http/Controllers/Auth/LoginController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Перенаправление в зависимости от роли
            switch ($user->role) {
                case 1: // Админ
                    return redirect()->intended(route('admin.dashboard'));
                case 2: // Врач
                    return redirect()->intended('/'); // Пока на главную
                case 3: // Регистратор
                    return redirect()->intended(route('admin.dashboard'));
                default:
                    return redirect()->intended('/');
            }
        }

        return back()->withErrors([
            'login' => 'Неверные данные для входа.',
        ])->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
