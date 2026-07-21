<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\WatchlistController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleController::class, 'redirect'])
        ->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'callback'])
        ->name('auth.google.callback');

    
    
    
    
    
    
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                \App\Models\ActivityLog::record('password.reset_requested', $user, $user->id);
            }

            return back()->with('status', 'Link reset password sudah dikirim ke email Anda.');
        }

        
        
        return back()->with('status', 'Jika email terdaftar, link reset password sudah dikirim.');
    })->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password/{token}', function (string $token, Request $request) {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                \App\Models\ActivityLog::record('password.reset', $user, $user->id);
            }
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password berhasil diganti, silakan login.');
        }

        return back()->withErrors(['email' => __($status)]);
    })->middleware('throttle:5,1')->name('password.update');
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        \App\Models\ActivityLog::record('login', null, Auth::id());

        return redirect()->intended(route('dashboard'));
    }

    return back()
        ->withErrors(['email' => 'Email atau password salah.'])
        ->onlyInput('email');
})->middleware(['throttle:5,1', 'guest'])->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    \App\Models\ActivityLog::record('register', null, $user->id);

    return redirect()->route('login')->with('status', 'Akun berhasil didaftarkan, silakan login.');
})->middleware(['throttle:5,1', 'guest'])->name('register.post');

Route::post('/logout', function (Request $request) {
    \App\Models\ActivityLog::record('logout', null, Auth::id());

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    
    
    
    Route::get('/countries', [DashboardController::class, 'countriesIndex'])->name('countries.index');
    Route::get('/countries/{id}', [DashboardController::class, 'countryDashboard'])->name('country.dashboard');
    
    Route::get('/dashboard/country/{id}', [DashboardController::class, 'countryDashboard'])->name('country.dashboard.legacy');

    
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics.index');

    Route::get('/weather', [DashboardController::class, 'weatherMonitoring'])->name('weather');
    Route::get('/currency', [DashboardController::class, 'currencyDashboard'])->name('currency');

    
    Route::get('/news', [DashboardController::class, 'newsDashboard'])->name('news.index');
    Route::get('/news/category/{category}', [DashboardController::class, 'newsByCategory'])->name('news.category');

    
    Route::get('/ports', [DashboardController::class, 'portDashboard'])->name('ports.index');
    Route::get('/comparison', [DashboardController::class, 'comparison'])->name('comparison');
    Route::post('/comparison', [DashboardController::class, 'comparisonData'])->name('comparison.data');
    Route::get('/watchlist', [DashboardController::class, 'watchlist'])->name('watchlist');

    
    Route::post('/api/watchlist/toggle/{countryId}', [WatchlistController::class, 'toggle'])->name('watchlist.toggle.id');
    Route::post('/api/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');
    Route::post('/api/watchlist/remove', [WatchlistController::class, 'remove'])->name('watchlist.remove');

    
    
    
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/ports', [AdminController::class, 'index'])->name('ports.index');
        Route::post('/ports', [AdminController::class, 'store'])->name('ports.store');
        Route::get('/ports/{id}/edit', [AdminController::class, 'edit'])->name('ports.edit');
        Route::put('/ports/{id}', [AdminController::class, 'update'])->name('ports.update');
        Route::delete('/ports/{id}', [AdminController::class, 'destroy'])->name('ports.destroy');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{id}/edit', [AdminController::class, 'usersEdit'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'usersUpdate'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'usersDestroy'])->name('users.destroy');
        Route::get('/analytics/user-behavior', [AdminController::class, 'userBehavior'])->name('analytics.behavior');
        Route::get('/analytics/user-behavior/{id}', [AdminController::class, 'userBehaviorDetail'])->name('analytics.behavior.user');

        
        Route::get('/articles', [AdminController::class, 'articlesIndex'])->name('articles.index');
        Route::get('/articles/create', [AdminController::class, 'articlesCreate'])->name('articles.create');
        Route::post('/articles', [AdminController::class, 'articlesStore'])->name('articles.store');
        Route::get('/articles/{id}/edit', [AdminController::class, 'articlesEdit'])->name('articles.edit');
        Route::put('/articles/{id}', [AdminController::class, 'articlesUpdate'])->name('articles.update');
        Route::delete('/articles/{id}', [AdminController::class, 'articlesDestroy'])->name('articles.destroy');
    });
});

