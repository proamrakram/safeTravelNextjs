<?php

namespace App\Services;

use App\Models\ContactMessage;

class ContactMessageService
{
    public const STATUSES = ['new', 'seen', 'closed'];

    public function data(array $filters, string $sortField, string $sortDirection, int $paginate = 10)
    {
        $q = ContactMessage::query();

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('message', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status']) && in_array($filters['status'], self::STATUSES, true)) {
            $q->where('status', $filters['status']);
        }

        $allowedSorts = ['id', 'name', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'id';
        }

        $sortDirection = $sortDirection === 'asc' ? 'asc' : 'desc';

        return $q->orderBy($sortField, $sortDirection)->paginate($paginate);
    }

    public function find(int $id): ?ContactMessage
    {
        return ContactMessage::find($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) return false;

        $msg = ContactMessage::find($id);
        if (!$msg) return false;

        $msg->status = $status;
        return (bool) $msg->save();
    }

    public function delete(int $id): bool
    {
        $msg = ContactMessage::find($id);
        if (!$msg) return false;

        return (bool) $msg->delete();
    }
}
