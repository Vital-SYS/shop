<?php

namespace App\Services;

use App\Models\Profile;

class ProfileService
{
    public function getUserProfiles()
    {
        return auth()->user()->profiles()->paginate(4);
    }

    public function createProfile(array $data)
    {
        return Profile::create($data);
    }

    public function validateUserProfile(Profile $profile)
    {
        if ($profile->user_id !== auth()->user()->id) {
            abort(404); // это чужой профиль
        }
    }

    public function updateProfile(array $data, Profile $profile)
    {
        $profile->update($data);
    }

    public function deleteProfile(Profile $profile)
    {
        $profile->delete();
    }
}
