<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Province;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    /**
     * Display a listing of beneficiaries (and pass provinces for the setup modal).
     */
    public function index()
    {
        // Paginate beneficiaries
        $beneficiaries = Beneficiary::orderBy('name')->paginate(15);

        // Load provinces for the "Add Setup" modal
        $provinces = Province::orderBy('name')->get();

        return view('beneficiaries.index', compact('beneficiaries', 'provinces'));
    }

    /**
     * Store a newly created beneficiary.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'spin_number' => 'required|string|unique:beneficiaries,spin_number',
            'name'        => 'required|string',
            'owner'       => 'nullable|string',
            'address'     => 'nullable|string',
        ]);

        Beneficiary::create($data);

        return redirect()
            ->route('beneficiaries.index')
            ->with('success', 'Beneficiary added.');
    }

    /**
     * Edit form.
     */
    public function edit(Beneficiary $beneficiary)
    {
        return view('beneficiaries.edit', compact('beneficiary'));
    }

    /**
     * Update a beneficiary.
     */
    public function update(Request $request, Beneficiary $beneficiary)
    {
        $data = $request->validate([
            'spin_number' => "required|string|unique:beneficiaries,spin_number,{$beneficiary->id}",
            'name'        => 'required|string',
            'owner'       => 'nullable|string',
            'address'     => 'nullable|string',
        ]);

        $beneficiary->update($data);

        return redirect()
            ->route('beneficiaries.index')
            ->with('success', 'Beneficiary updated.');
    }

    /**
     * Delete a beneficiary.
     */
    public function destroy(Beneficiary $beneficiary)
    {
        $beneficiary->delete();

        return redirect()
            ->route('beneficiaries.index')
            ->with('success', 'Beneficiary deleted.');
    }
}
