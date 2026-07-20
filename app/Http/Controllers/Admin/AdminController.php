<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Get statistics from database
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_ports' => \App\Models\Port::count(),
            'total_articles' => \App\Models\Article::count(),
            'active_ports' => \App\Models\Port::where('is_active', true)->count(),
            'published_articles' => \App\Models\Article::where('status', 'published')->count(),
            'admin_users' => \App\Models\User::where('role', 'admin')->count(),
            'regular_users' => \App\Models\User::where('role', 'user')->count(),
        ];

        // Get recent activities (mix of users, ports, and articles)
        $recentActivities = collect();

        // Recent users
        $recentUsers = \App\Models\User::latest()->take(2)->get()->map(function($user) {
            return [
                'time' => $user->created_at,
                'user' => 'Admin',
                'action' => "Created new user: {$user->name}",
                'status' => 'success',
                'icon' => 'user-plus'
            ];
        });

        // Recent ports
        $recentPorts = \App\Models\Port::latest('updated_at')->take(2)->get()->map(function($port) {
            return [
                'time' => $port->updated_at,
                'user' => 'Admin',
                'action' => "Updated port: {$port->port_name}",
                'status' => 'success',
                'icon' => 'anchor'
            ];
        });

        // Recent articles
        $recentArticles = \App\Models\Article::latest()->take(2)->get()->map(function($article) {
            return [
                'time' => $article->created_at,
                'user' => 'Admin',
                'action' => "Published article: " . \Illuminate\Support\Str::limit($article->title, 30),
                'status' => $article->status === 'published' ? 'success' : 'draft',
                'icon' => 'newspaper'
            ];
        });

        // Merge and sort by time
        $recentActivities = $recentUsers->merge($recentPorts)->merge($recentArticles)
            ->sortByDesc('time')
            ->take(5)
            ->values();

        return view('admin.index', compact('stats', 'recentActivities'));
    }
}
