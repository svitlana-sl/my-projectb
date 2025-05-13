<?php

namespace App\Http\Controllers;

use App\Models\SitterService;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class SitterServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SitterService::with('serviceType');
        
        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }
        
        $services = $query->paginate(10);
        $serviceTypes = ServiceType::pluck('name', 'id');
        
        return view('sitter-services.index', compact('services', 'serviceTypes'));
    }

    public function create()
    {
        $serviceTypes = ServiceType::pluck('name', 'id');
        return view('sitter-services.create', compact('serviceTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'hourly_rate' => 'required|numeric|min:0',
            'service_type_id' => 'required|exists:service_types,id',
        ]);

        $service = SitterService::create($request->all());
        return redirect()->route('sitter-services.index')
            ->with('success', 'Service created successfully.');
    }

    public function show(SitterService $sitterService)
    {
        return view('sitter-services.show', compact('sitterService'));
    }

    public function edit(SitterService $sitterService)
    {
        $serviceTypes = ServiceType::pluck('name', 'id');
        return view('sitter-services.edit', compact('sitterService', 'serviceTypes'));
    }

    public function update(Request $request, SitterService $sitterService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'hourly_rate' => 'required|numeric|min:0',
            'service_type_id' => 'required|exists:service_types,id',
        ]);

        $sitterService->update($request->all());
        return redirect()->route('sitter-services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(SitterService $sitterService)
    {
        $sitterService->delete();
        return redirect()->route('sitter-services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
