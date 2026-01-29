<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function contactMessage(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $row = ContactMessage::create([
            'name' => $data['name'],
            'message' => $data['message'],
            // status default = new من المايغريشن
        ]);

        return response()->json([
            'message' => 'Message received successfully.',
            'data' => [
                'id' => $row->id,
                'status' => $row->status,
                'created_at' => optional($row->created_at)->toISOString(),
            ],
        ], 201);
    }
}
