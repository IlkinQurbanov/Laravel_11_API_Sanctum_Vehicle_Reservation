<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class DriverController extends Controller
{
     // Получение списка водителей
     public function index(): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

         $drivers = Driver::all();
         return response()->json($drivers);
     }
 
     // Получение информации о конкретном водителе
     public function show($id): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $driver = Driver::find($id);
 
         if (!$driver) {
             return response()->json(['error' => 'Driver not found'], 404);
         }
 
         return response()->json($driver);
     }
 
     // Создание нового водителя
     public function store(Request $request): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $validatedData = $request->validate([
             'name' => 'required|string|max:255',
         ]);
 
         $driver = Driver::create($validatedData);
 
         return response()->json($driver, 201);
     }
 
     // Обновление существующего водителя
     public function update(Request $request, $id): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $driver = Driver::find($id);
 
         if (!$driver) {
             return response()->json(['error' => 'Driver not found'], 404);
         }
 
         $validatedData = $request->validate([
             'name' => 'required|string|max:255',
         ]);
 
         $driver->update($validatedData);
 
         return response()->json($driver);
     }
 
     // Удаление водителя
     public function destroy($id): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $driver = Driver::find($id);
 
         if (!$driver) {
             return response()->json(['error' => 'Driver not found'], 404);
         }
 
         $driver->delete();
 
         return response()->json(['message' => 'Driver deleted successfully']);
     }
}
