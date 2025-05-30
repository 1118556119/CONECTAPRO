<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::where('user_id', Auth::id())->get();
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|in:correctivo,preventivo,instalacion,limpieza,reparacion,asesoria,otro',
            'urgency' => 'required|in:baja,media,alta,critica',
            'description' => 'required|string|max:1000',
            'equipment_type' => 'required|string',
            'brand' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable|string|in:,morning,afternoon,evening',
            'comments' => 'nullable|string|max:1000',
            'service_type_detail' => 'required_if:type,otro|nullable|string|max:255',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $serviceRequest = ServiceRequest::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'urgency' => $request->urgency,
            'description' => $request->description,
            'equipment_type' => $request->equipment_type,
            'brand' => $request->brand,
            'address' => $request->address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'comments' => $request->comments,
            'service_type_detail' => $request->type === 'otro' ? $request->service_type_detail : null,
            'status' => 'pending',
        ]);
        
        // Procesar fotos si se han enviado
        if ($request->hasFile('photos')) {
            $this->processPhotos($request, $serviceRequest);
        }

        return response()->json($serviceRequest, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);
        if ($serviceRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $serviceRequest->update(['status' => $request->status]);
        return response()->json($serviceRequest);
    }

    // Método para procesar fotos
    private function processPhotos(Request $request, ServiceRequest $serviceRequest)
    {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('service_requests', 'public');
            
            // Aquí necesitarías tener un modelo ServiceRequestPhoto
            $serviceRequest->photos()->create([
                'file_path' => $path,
                'file_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getClientMimeType(),
                'file_size' => $photo->getSize()
            ]);
        }
    }
}