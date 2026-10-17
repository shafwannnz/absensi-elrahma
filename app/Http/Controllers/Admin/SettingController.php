<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\CampusSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Pengaturan titik kampus, radius, dan jam absensi. */
class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', ['setting' => CampusSetting::current()]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        CampusSetting::current()->update($request->validated());

        return back()->with('success', 'Pengaturan lokasi & jam absensi disimpan.');
    }
}
