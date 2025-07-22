<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewType;
use Illuminate\Http\Request;

class ReviewTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviewTypes = ReviewType::all();
        return view('admin.review-types.index', compact('reviewTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.review-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:review_types',
            'description' => 'nullable|string|max:1000',
            'color_code' => 'nullable|string|max:7'
        ]);

        ReviewType::create($request->all());

        return redirect()->route('admin.review-types.index')
            ->with('success', 'Review type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReviewType $reviewType)
    {
        return view('admin.review-types.show', compact('reviewType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReviewType $reviewType)
    {
        return view('admin.review-types.edit', compact('reviewType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReviewType $reviewType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:review_types,name,' . $reviewType->id,
            'description' => 'nullable|string|max:1000',
            'color_code' => 'nullable|string|max:7'
        ]);

        $reviewType->update($request->all());

        return redirect()->route('admin.review-types.index')
            ->with('success', 'Review type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReviewType $reviewType)
    {
        $reviewType->delete();

        return redirect()->route('admin.review-types.index')
            ->with('success', 'Review type deleted successfully.');
    }
}
