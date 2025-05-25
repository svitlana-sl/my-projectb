<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;



class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceTypes = ServiceType::all();
        return view('service-types.index', compact('serviceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('service-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name',
            'description' => 'nullable|string|max:1000',
        ]);

        ServiceType::create($request->all());
        return redirect()->route('service-types.index')
            ->with('success', 'Service type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceType $serviceType)
    {
        return view('service-types.show', compact('serviceType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceType $serviceType)
    {
        return view('service-types.edit', compact('serviceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceType $serviceType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name,' . $serviceType->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $serviceType->update($request->all());
        return redirect()->route('service-types.index')
            ->with('success', 'Service type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();
        return redirect()->route('service-types.index')
            ->with('success', 'Service type deleted successfully.');
    }
}
