<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $roleMeta = [
            'admin' => [
                'label' => 'Admins',
                'chip' => 'bg-indigo-600 text-indigo-50',
                'tone' => 'from-indigo-50 to-white',
                'border' => 'border-indigo-100',
                'pill' => 'bg-indigo-100 text-indigo-700',
            ],
            'tenant' => [
                'label' => 'Tenants',
                'chip' => 'bg-emerald-600 text-emerald-50',
                'tone' => 'from-emerald-50 to-white',
                'border' => 'border-emerald-100',
                'pill' => 'bg-emerald-100 text-emerald-700',
            ],
            'caretaker' => [
                'label' => 'Caretakers',
                'chip' => 'bg-amber-600 text-amber-50',
                'tone' => 'from-amber-50 to-white',
                'border' => 'border-amber-100',
                'pill' => 'bg-amber-100 text-amber-800',
            ],
            'osas' => [
                'label' => 'OSAS',
                'chip' => 'bg-purple-600 text-purple-50',
                'tone' => 'from-purple-50 to-white',
                'border' => 'border-purple-100',
                'pill' => 'bg-purple-100 text-purple-800',
            ],
        ];

        $roleStats = collect($roleMeta)->map(function ($meta, $role) {
            $count = User::query()
                ->where(function ($q) use ($role) {
                    $q->where('role', $role);

                    if ($role === 'admin') {
                        // legacy owners behave as admins
                        $q->orWhere('role', 'owner');
                    }
                })
                ->orWhereHas('roles', fn ($q) => $q->where('name', $role))
                ->distinct('id')
                ->count();

            return array_merge($meta, ['count' => $count]);
        });

        $recentUsers = User::query()
            ->with('roles')
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']);

        $allUsers = User::query()
            ->with('roles')
            ->latest()
            ->paginate(12, ['id', 'name', 'email', 'role', 'is_active']);

        $totals = [
            'all' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'pending' => User::where('is_active', false)->count(),
        ];

        return view('admin.dashboard', [
            'roleStats'   => $roleStats,
            'recentUsers' => $recentUsers,
            'allUsers'    => $allUsers,
            'totals'      => $totals,
        ]);
    }
}
