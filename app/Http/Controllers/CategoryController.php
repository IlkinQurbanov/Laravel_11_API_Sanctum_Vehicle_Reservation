<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;




class CategoryController extends Controller
{
     // Получение списка категорий
     public function index(): JsonResponse
     {
      
         $categories = Category::all();
         return response()->json($categories);
     }
 
     // Получение информации о конкретной категории
     public function show($id): JsonResponse
     {
         $category = Category::find($id);
 
         if (!$category) {
             return response()->json(['error' => 'Category not found'], 404);
         }
 
         return response()->json($category);
     }
 
     // Создание новой категории
     public function store(Request $request): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $validatedData = $request->validate([
             'name' => 'required|string|max:255',
         ]);
 
         $category = Category::create($validatedData);
 
         return response()->json($category, 201);
     }
 
     // Обновление существующей категории
     public function update(Request $request, $id): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $category = Category::find($id);
 
         if (!$category) {
             return response()->json(['error' => 'Category not found'], 404);
         }
 
         $validatedData = $request->validate([
             'name' => 'required|string|max:255',
         ]);
 
         $category->update($validatedData);
 
         return response()->json($category);
     }
 
     // Удаление категории
     public function destroy($id): JsonResponse
     {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
         $category = Category::find($id);
 
         if (!$category) {
             return response()->json(['error' => 'Category not found'], 404);
         }
 
         $category->delete();
 
         return response()->json(['message' => 'Category deleted successfully']);
     }
}
