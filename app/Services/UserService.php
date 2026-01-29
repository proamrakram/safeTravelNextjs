<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function data(array $filters, string $sortField, string $sortDirection, int $paginate = 10)
    {
        $q = User::query();

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('username', 'like', "%{$s}%");
            });
        }

        // حماية بسيطة من sort غير مسموح
        $allowedSorts = ['id', 'name', 'email', 'username', 'created_at'];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'id';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        return $q->orderBy($sortField, $sortDirection)
            ->paginate($paginate);
    }

    public function delete(int $id): bool
    {
        $user = User::find($id);
        if (!$user) return false;
        return (bool) $user->delete();
    }
}
