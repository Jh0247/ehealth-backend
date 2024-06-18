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

        $response = [
            'profile_img' => $user->profile_img,
            'name' => $user->name,
            'icno' => $user->icno,
            'email' => $user->email,
            'contact' => $user->contact,
            'health_record' => $user->healthRecord
        ];
    
        return response()->json($response);
    }

    public function getSpecificUserHealthRecord($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->load('healthRecord');

        if (!$user->healthRecord) {
            return response()->json(['error' => 'Health record not found for the specified user.'], 404);
        }

        $response = [
            'profile_img' => $user->profile_img,
            'name' => $user->name,
            'icno' => $user->icno,
            'email' => $user->email,
            'contact' => $user->contact,
            'health_record' => $user->healthRecord
        ];
    
        return response()->json($response);
    }
}
