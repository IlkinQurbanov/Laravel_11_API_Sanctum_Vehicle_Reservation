<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\Driver;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class VehicleController extends Controller
{
    public function availableVehicles(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized: No user authenticated'], 401);
        }

        return response()->json([
            'message' => 'User is authenticated',
            'user' => $user,
            'token' => $request->bearerToken()
        ]);

     
        $category_ids = $user->availableCategories();

        $vehicles = Vehicle::with('category', 'driver')
            ->whereIn('category_id', $category_ids)
            ->when($request->has('model'), function ($query) use ($request) {
                $query->where('model', 'like', '%' . $request->input('model') . '%');
            })
            ->whereDoesntHave('reservations', function ($query) use ($request) {
                $query->where('start_time', '<=', $request->input('end_time'))
                      ->where('end_time', '>=', $request->input('start_time'));
            })
            ->get();

        return response()->json($vehicles);
    }
    public function filterByCategory(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        // Debugging: Check if category_id is correctly received
        $categoryId = $validatedData['category_id'];
        $vehicles = Vehicle::where('category_id', $categoryId)->get();

        // Debugging: Check the query and results
        \Log::info('Category ID: ' . $categoryId);
        \Log::info('Vehicles: ', $vehicles->toArray());

        if ($vehicles->isEmpty()) {
            return response()->json(['message' => 'No vehicles found for the specified category.'], 404);
        }

        return response()->json($vehicles);
    }

     public function index(): JsonResponse
     {
         $vehicles = Vehicle::with('category', 'driver')->get();
         return response()->json($vehicles);
     }
 
     public function show($id): JsonResponse
     {
         $vehicle = Vehicle::with('category', 'driver')->find($id);
 
         if (!$vehicle) {
             return response()->json(['error' => 'Vehicle not found'], 404);
         }
 
         return response()->json($vehicle);
     }
 


     public function store(Request $request): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         try {
             $validatedData = $request->validate([
                 'model' => 'required|string|max:255',
                 'category_id' => 'required|exists:categories,id',
                 'driver_id' => 'required|exists:drivers,id',
             ], [
                 'model.required' => 'The vehicle model is required.',
                 'model.string' => 'The vehicle model must be a string.',
                 'model.max' => 'The vehicle model may not be greater than 255 characters.',
                 'category_id.required' => 'The category ID is required.',
                 'category_id.exists' => 'The selected category ID is invalid.',
                 'driver_id.required' => 'The driver ID is required.',
                 'driver_id.exists' => 'The selected driver ID is invalid.',
             ]);
         
             $vehicle = Vehicle::create($validatedData);
         
             return response()->json($vehicle, 201);
         } catch (\Illuminate\Validation\ValidationException $e) {
             Log::error('Validation Error: ' . json_encode($e->errors()));
             return response()->json($e->errors(), 422);
         }
     }
     
     
 
     // Обновление существующего автомобиля
     public function update(Request $request, $id): JsonResponse
     {
       
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        } try {
             $vehicle = Vehicle::find($id);
         
             if (!$vehicle) {
                 return response()->json(['error' => 'Vehicle not found'], 404);
             }
         
             $validatedData = $request->validate([
                 'model' => 'required|string|max:255',
                 'category_id' => 'required|exists:categories,id',
                 'driver_id' => 'required|exists:drivers,id',
             ], [
                 'model.required' => 'The vehicle model is required.',
                 'model.string' => 'The vehicle model must be a string.',
                 'model.max' => 'The vehicle model may not be greater than 255 characters.',
                 'category_id.required' => 'The category ID is required.',
                 'category_id.exists' => 'The selected category ID is invalid.',
                 'driver_id.required' => 'The driver ID is required.',
                 'driver_id.exists' => 'The selected driver ID is invalid.',
             ]);
         
             $vehicle->update($validatedData);
         
             return response()->json($vehicle);
         } catch (\Illuminate\Validation\ValidationException $e) {
             Log::error('Validation Error: ' . json_encode($e->errors()));
         
             return response()->json($e->errors(), 422);
         }
     }
 
     public function destroy($id): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $vehicle = Vehicle::find($id);
 
         if (!$vehicle) {
             return response()->json(['error' => 'Vehicle not found'], 404);
         }
 
         $vehicle->delete();
 
         return response()->json(['message' => 'Vehicle deleted successfully']);
     }
}
