<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
   public function index(Request $request)
{
    $query = Category::whereNull('user_id');

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    $categories = $query
        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}
}
