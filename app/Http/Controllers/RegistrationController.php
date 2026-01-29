<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\FamilyMember;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        // ✅ 1) Validate request
        $validated = $request->validate([
            'full_name'      => ['required', 'string', 'max:255'],
            'age'            => ['required', 'integer', 'min:1', 'max:120'],
            'gender'         => ['required', Rule::in(['Male', 'Female'])],
            'email'          => ['required', 'email', 'max:255'],

            'travelers'      => ['nullable', 'integer', 'min:1', 'max:999'],
            'destination'    => ['required', 'string', 'max:255'],
            'stay_duration'  => ['required', 'integer', 'min:1', 'max:3650'],
            'travel_package' => ['required', Rule::in(['Economic', 'Comfortable', 'VIP'])],

            // family_members as JSON string OR array (React ممكن يرسلها string)
            'family_members' => ['nullable'],

            // documents (files)
            'personal_photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
            'passport'       => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'id_card'        => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        // ✅ 2) Parse family members
        $familyMembers = $request->input('family_members');

        if (is_string($familyMembers) && $familyMembers !== '') {
            $familyMembers = json_decode($familyMembers, true);
        }

        if ($familyMembers === null) {
            $familyMembers = [];
        }

        if (!is_array($familyMembers)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid family_members format',
            ], 422);
        }

        // Validate each family member (اختياري لكن مهم)
        foreach ($familyMembers as $i => $m) {
            if (!is_array($m)) {
                return response()->json([
                    'ok' => false,
                    'message' => "Invalid family member at index {$i}",
                ], 422);
            }

            $name = $m['name'] ?? '';
            $age = $m['age'] ?? null;
            $gender = $m['gender'] ?? '';

            if ($name === '' || !is_numeric($age) || (int)$age < 0 || (int)$age > 120 || !in_array($gender, ['Male', 'Female'], true)) {
                return response()->json([
                    'ok' => false,
                    'message' => "Invalid family member data at index {$i}",
                ], 422);
            }
        }

        // ✅ 3) Transaction: create registration + family members + documents
        $disk = 'public'; // storage/app/public
        $storedPaths = [];

        try {
            $result = DB::transaction(function () use ($request, $validated, $familyMembers, $disk, &$storedPaths) {

                // A) Create Registration
                $registration = Registration::create([
                    'full_name'      => $validated['full_name'],
                    'age'            => (int) $validated['age'],
                    'gender'         => $validated['gender'],
                    'email'          => $validated['email'],
                    'travelers'      => (int) ($validated['travelers'] ?? 1),
                    'destination'    => $validated['destination'],
                    'stay_duration'  => (int) $validated['stay_duration'],
                    'travel_package' => $validated['travel_package'],
                    // status/admin_notes defaults
                ]);

                // B) Create Family Members
                if (count($familyMembers) > 0) {
                    $rows = [];
                    foreach ($familyMembers as $m) {
                        $rows[] = [
                            'registration_id' => $registration->id,
                            'name' => $m['name'],
                            'age' => (int) $m['age'],
                            'gender' => $m['gender'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    FamilyMember::insert($rows);
                }

                // C) Store Documents + create Document rows
                $docMap = [
                    'personal_photo' => 'photo',
                    'passport'       => 'passport',
                    'id_card'        => 'id_card',
                ];

                foreach ($docMap as $inputName => $type) {
                    $file = $request->file($inputName);

                    // مسار منظم لكل تسجيل
                    $path = $file->store("registrations/{$registration->id}", $disk);
                    $storedPaths[] = $path;

                    Document::create([
                        'registration_id' => $registration->id,
                        'type'           => $type,
                        'path'           => $path,
                        'original_name'  => $file->getClientOriginalName(),
                        'mime'           => $file->getClientMimeType(),
                        'size'           => $file->getSize(),
                    ]);
                }

                return $registration;
            });

            return response()->json([
                'ok' => true,
                'message' => 'Registration created successfully',
                'data' => [
                    'id' => $result->id,
                    'status' => $result->status,
                ],
            ], 201);
        } catch (\Throwable $e) {
            // لو فشل transaction بعد ما خزّنا ملفات: نحذف الملفات اللي خزّناها
            if (!empty($storedPaths)) {
                foreach ($storedPaths as $p) {
                    Storage::disk($disk)->delete($p);
                }
            }

            return response()->json([
                'ok' => false,
                'message' => 'Failed to create registration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
