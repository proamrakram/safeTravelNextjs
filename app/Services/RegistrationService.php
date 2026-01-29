<?php

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function data(array $filters, string $sortField, string $sortDirection, int $paginate = 10)
    {
        $q = Registration::query()->withCount(['familyMembers', 'documents']);

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $q->where(function ($qq) use ($s) {
                $qq->where('full_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status'])) $q->where('status', $filters['status']);
        if (!empty($filters['gender'])) $q->where('gender', $filters['gender']);
        if (!empty($filters['travel_package'])) $q->where('travel_package', $filters['travel_package']);

        $allowedSorts = [
            'id',
            'full_name',
            'age',
            'gender',
            'email',
            'travelers',
            'destination',
            'stay_duration',
            'travel_package',
            'status',
            'created_at'
        ];

        if (!in_array($sortField, $allowedSorts, true)) $sortField = 'id';
        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        return $q->orderBy($sortField, $sortDirection)->paginate($paginate);
    }

    public function findWithDetails(int $id): ?Registration
    {
        return Registration::with(['familyMembers', 'documents'])->find($id);
    }

    public function updateStatus(int $id, string $status, ?string $adminNotes = null): bool
    {
        $allowed = ['pending', 'processing', 'approved', 'rejected'];
        if (!in_array($status, $allowed, true)) return false;

        return DB::transaction(function () use ($id, $status, $adminNotes) {
            $reg = Registration::lockForUpdate()->find($id);
            if (!$reg) return false;

            $reg->status = $status;

            // خيارياً حدّث الملاحظات بنفس العملية
            if ($adminNotes !== null) {
                $reg->admin_notes = $adminNotes !== '' ? $adminNotes : null;
            }

            return (bool) $reg->save();
        });
    }

    public function updateNotes(int $id, ?string $adminNotes): bool
    {
        $reg = Registration::find($id);
        if (!$reg) return false;

        $reg->admin_notes = $adminNotes !== '' ? $adminNotes : null;
        return (bool) $reg->save();
    }

    public function delete(int $id): bool
    {
        $row = Registration::find($id);
        if (!$row) return false;

        return (bool) $row->delete();
    }
}
