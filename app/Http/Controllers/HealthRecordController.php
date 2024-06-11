<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\HealthRecord\HealthRecordRepositoryInterface;
use Illuminate\Http\Request;

class HealthRecordController extends Controller
{
    protected $healthRecordRepository;

    public function __construct(HealthRecordRepositoryInterface $healthRecordRepository)
    {
        $this->healthRecordRepository = $healthRecordRepository;
    }

    public function getUserHealthRecord(Request $request)
    {
        $user = $request->user;

        if (!$user) {
            return response()->json(['error' => 'User not found in request.'], 401);
        }

        $user = User::find($user->id)->load('healthRecord');

        return response()->json([
            'health-record' => $user->healthRecord,
        ]);
    }
}
