<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    private $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        $profiles = $this->profileService->getUserProfiles();
        return view('user.profile.index', compact('profiles'));
    }

    public function store(ProfileRequest $request)
    {
        $this->profileService->createProfile($request->all());
        return redirect()
            ->route('user.profile.index')
            ->with('success', 'Новый профиль успешно создан');
    }

    public function create()
    {
        return view('user.profile.create');
    }

    public function show(Profile $profile)
    {
        $this->profileService->validateUserProfile($profile);
        return view('user.profile.show', compact('profile'));
    }

    public function edit(Profile $profile)
    {
        $this->profileService->validateUserProfile($profile);
        return view('user.profile.edit', compact('profile'));
    }

    public function update(ProfileRequest $request, Profile $profile)
    {
        $this->profileService->updateProfile($request->all(), $profile);
        return redirect()
            ->route('user.profile.show', ['profile' => $profile->id])
            ->with('success', 'Профиль был успешно отредактирован');
    }

    public function destroy(Profile $profile)
    {
        $this->profileService->validateUserProfile($profile);
        $this->profileService->deleteProfile($profile);
        return redirect()
            ->route('user.profile.index')
            ->with('success', 'Профиль был успешно удален');
    }
}
