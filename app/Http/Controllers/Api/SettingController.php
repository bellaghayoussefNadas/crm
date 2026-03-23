<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function get()
    {
        return response()->json(Setting::all()->pluck('value', 'key'));
    }

    public function update(Request $request)
    {
        $settings = $request->except('logo');
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $settings['logo_url'] = asset('storage/' . $path);
        }

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }
        return response()->json(['message' => 'Paramètres mis à jour']);
    }
}
