<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Port;
use App\Models\Article;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Kelas AdminController: admin controller
class AdminController extends Controller
{
    // dashboard
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    
    
    
    
    
    
    
    

    // user behavior
    public function userBehavior(Request $request)
    {
        
        $days = (int) $request->query('days', 30);
        $days = in_array($days, [7, 30, 90, 365]) ? $days : 30;
        $since = now()->subDays($days);

        
        $topCountries = ActivityLog::query()
            ->where('action', 'country.viewed')
            ->where('created_at', '>=', $since)
            ->select('subject_id', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT user_id) as unique_viewers'))
            ->groupBy('subject_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        
        $countryNames = Country::whereIn('id', $topCountries->pluck('subject_id'))
            ->get(['id', 'name', 'code', 'flag_url'])
            ->keyBy('id');

        $topCountries = $topCountries->map(function ($row) use ($countryNames) {
            $country = $countryNames->get($row->subject_id);
            return [
                'country' => $country,
                'views' => $row->views,
                'unique_viewers' => $row->unique_viewers,
            ];
        })->filter(fn($row) => $row['country'] !== null)->values();

        
        
        
        $topUsers = ActivityLog::query()
            ->where('created_at', '>=', $since)
            ->whereNotNull('user_id')
            ->whereHas('user', fn($q) => $q->where('is_admin', false))
            ->select('user_id', DB::raw('COUNT(*) as total_actions'))
            ->groupBy('user_id')
            ->orderByDesc('total_actions')
            ->limit(10)
            ->with('user:id,name,email')
            ->get();

        
        $actionBreakdown = ActivityLog::query()
            ->where('created_at', '>=', $since)
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->get();

        
        $dailyRaw = ActivityLog::query()
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->pluck('total', 'day');

        $dailyTrend = [];
        for ($d = $since->copy(); $d->lte(now()); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $dailyTrend[] = ['date' => $key, 'total' => (int) ($dailyRaw[$key] ?? 0)];
        }

        
        $summary = [
            'total_actions' => ActivityLog::where('created_at', '>=', $since)->count(),
            'active_users' => ActivityLog::where('created_at', '>=', $since)
                ->whereNotNull('user_id')
                ->whereHas('user', fn($q) => $q->where('is_admin', false))
                ->distinct('user_id')->count('user_id'),
            'country_views' => ActivityLog::where('action', 'country.viewed')->where('created_at', '>=', $since)->count(),
            'total_users' => User::where('is_admin', false)->count(),
        ];

        
        $recentLogs = ActivityLog::with('user:id,name,email')
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.user-behavior', compact(
            'days', 'topCountries', 'topUsers', 'actionBreakdown',
            'dailyTrend', 'summary', 'recentLogs'
        ));
    }

    
    // user behavior detail
    public function userBehaviorDetail(int $id)
    {
        $user = User::findOrFail($id);

        $logs = ActivityLog::where('user_id', $id)
            ->latest()
            ->paginate(25);

        $favoriteCountries = ActivityLog::where('user_id', $id)
            ->where('action', 'country.viewed')
            ->select('subject_id', DB::raw('COUNT(*) as views'))
            ->groupBy('subject_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $countryNames = Country::whereIn('id', $favoriteCountries->pluck('subject_id'))
            ->get(['id', 'name', 'code', 'flag_url'])
            ->keyBy('id');

        $favoriteCountries = $favoriteCountries->map(function ($row) use ($countryNames) {
            return [
                'country' => $countryNames->get($row->subject_id),
                'views' => $row->views,
            ];
        })->filter(fn($row) => $row['country'] !== null)->values();

        return view('admin.user-behavior-detail', compact('user', 'logs', 'favoriteCountries'));
    }

    // index
    public function index()
    {
        
        $ports = Port::paginate(20, ['*'], 'page', null, null);
        return view('admin.ports', compact('ports'));
    }

    // store
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'country' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $port = Port::create($validated);
        \App\Models\ActivityLog::record('port.created', $port);

        return redirect()->back()->with('success', 'Port created successfully');
    }

    // edit
    public function edit(int $id)
    {
        $port = Port::findOrFail($id);
        return view('admin.ports-edit', compact('port'));
    }

    // update
    public function update(Request $request, int $id)
    {
        $port = Port::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string',
            'country' => 'string',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'status' => 'in:active,inactive,maintenance',
        ]);
        $port->update($validated);
        \App\Models\ActivityLog::record('port.updated', $port);

        return redirect()->back()->with('success', 'Port updated successfully');
    }

    // destroy
    public function destroy(int $id)
    {
        $port = Port::findOrFail($id);
        \App\Models\ActivityLog::record('port.deleted', $port);
        $port->delete();

        return redirect()->back()->with('success', 'Port deleted successfully');
    }

    // users
    public function users()
    {
        $users = User::query()->paginate(20);
        return view('admin.users', compact('users'));
    }

    // users edit
    public function usersEdit(int $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users-edit', compact('user'));
    }

    // users update
    public function usersUpdate(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'is_admin' => 'nullable|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        
        
        
        if ($user->id !== auth()->id()) {
            $user->is_admin = $request->boolean('is_admin');
        }

        if (! empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->save();

        \App\Models\ActivityLog::record('user.updated', $user, null, ['name' => $user->name, 'email' => $user->email]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    // users destroy
    public function usersDestroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        \App\Models\ActivityLog::record('user.deleted', $user, null, ['name' => $user->name, 'email' => $user->email]);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    
    
    

    // articles index
    public function articlesIndex()
    {
        $articles = Article::latest()->paginate(20);
        return view('admin.articles', compact('articles'));
    }

    // articles create
    public function articlesCreate()
    {
        return view('admin.articles-create');
    }

    // articles store
    public function articlesStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'image_url' => 'nullable|url',
            'tags' => 'nullable|string', 
            'is_published' => 'nullable|boolean',
        ]);

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'author' => $request->author,
            'category' => $request->category,
            'image_url' => $request->image_url,
            'tags' => $request->tags
                ? array_map('trim', explode(',', $request->tags))
                : null,
            'is_published' => $request->boolean('is_published', true),
        ]);

        \App\Models\ActivityLog::record('article.created', $article, null, ['title' => $article->title]);

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully');
    }

    // articles edit
    public function articlesEdit(int $id)
    {
        $article = Article::findOrFail($id);
        return view('admin.articles-edit', compact('article'));
    }

    // articles update
    public function articlesUpdate(Request $request, int $id)
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'image_url' => 'nullable|url',
            'tags' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $article->update([
            'title' => $request->title,
            'content' => $request->content,
            'author' => $request->author,
            'category' => $request->category,
            'image_url' => $request->image_url,
            'tags' => $request->tags
                ? array_map('trim', explode(',', $request->tags))
                : null,
            'is_published' => $request->boolean('is_published', true),
        ]);

        \App\Models\ActivityLog::record('article.updated', $article, null, ['title' => $article->title]);

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully');
    }

    // articles destroy
    public function articlesDestroy(int $id)
    {
        $article = Article::findOrFail($id);
        \App\Models\ActivityLog::record('article.deleted', $article, null, ['title' => $article->title]);
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted successfully');
    }
}

