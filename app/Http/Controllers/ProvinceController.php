<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function index()
    {
        $provinces = Province::orderBy('name')->paginate(15);
        return view('provinces.index', compact('provinces'));
    }

    public function create()
    {
        return view('provinces.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:provinces,name',
        ]);

        Province::create(['name' => $request->name]);
        return redirect()->route('provinces.index')
                         ->with('success', 'Province added.');
    }

    public function edit(Province $province)
    {
        return view('provinces.edit', compact('province'));
    }

    public function update(Request $request, Province $province)
    {
        $request->validate([
            'name' => "required|string|unique:provinces,name,{$province->id}",
        ]);

        $province->update(['name' => $request->name]);
        return redirect()->route('provinces.index')
                         ->with('success', 'Province updated.');
    }

    public function destroy(Province $province)
    {
        $province->delete();
        return redirect()->route('provinces.index')
                         ->with('success', 'Province deleted.');
    }
}
