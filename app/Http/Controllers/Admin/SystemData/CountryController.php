<?php

namespace App\Http\Controllers\Admin\SystemData;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:manage countries']);
    }

    /**
     * Display a listing of countries.
     */
    public function index()
    {
        $countries = Country::orderBy('name')->paginate(15);
        return view('admin.system-data.countries.index', compact('countries'));
    }

    /**
     * Show the form for creating a new country.
     */
    public function create()
    {
        return view('admin.system-data.countries.create');
    }

    /**
     * Store a newly created country in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries',
            'code' => 'required|string|max:3|unique:countries',
            'iso_code' => 'required|string|max:2|unique:countries',
            'phone_code' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:3',
            'flag' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        Country::create($request->all());

        return redirect()->route('admin.countries.index')
            ->with('success', 'Country created successfully.');
    }

    /**
     * Display the specified country.
     */
    public function show(Country $country)
    {
        return view('admin.system-data.countries.show', compact('country'));
    }

    /**
     * Show the form for editing the specified country.
     */
    public function edit(Country $country)
    {
        return view('admin.system-data.countries.edit', compact('country'));
    }

    /**
     * Update the specified country in storage.
     */
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
            'code' => 'required|string|max:3|unique:countries,code,' . $country->id,
            'iso_code' => 'required|string|max:2|unique:countries,iso_code,' . $country->id,
            'phone_code' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:3',
            'flag' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $country->update($request->all());

        return redirect()->route('admin.countries.index')
            ->with('success', 'Country updated successfully.');
    }

    /**
     * Remove the specified country from storage.
     */
    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('admin.countries.index')
            ->with('success', 'Country deleted successfully.');
    }

    /**
     * Toggle country status (active/inactive)
     */
    public function toggleStatus(Country $country)
    {
        $country->update([
            'is_active' => !$country->is_active
        ]);

        $status = $country->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.countries.index')
            ->with('success', "Country {$status} successfully.");
    }
}
