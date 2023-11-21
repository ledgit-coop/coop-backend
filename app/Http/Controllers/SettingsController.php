<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index() {
        return response()->json(Setting::get());
    }

    public function store(Request $request) {
        
        $this->validate($request, [
            '*.name' => 'required',
            '*.key' => 'required',
            '*.value' => 'required'
        ]);

        Setting::upsert($request->all(), ['key', 'name'], ['value']);

        return response()->json(Setting::get());
    }
}
