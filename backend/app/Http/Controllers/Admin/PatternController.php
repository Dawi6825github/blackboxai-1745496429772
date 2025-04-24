<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PatternRequest;
use App\Models\Pattern;
use Illuminate\Http\Request;

class PatternController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $patterns = Pattern::all();
        
        return response()->json($patterns);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Admin\PatternRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PatternRequest $request)
    {
        $pattern = Pattern::create([
            'name' => $request->name,
            'description' => $request->description,
            'grid' => json_encode($request->grid),
        ]);

        return response()->json($pattern, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $pattern = Pattern::findOrFail($id);
        $pattern->grid = json_decode($pattern->grid);
        
        return response()->json($pattern);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\PatternRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PatternRequest $request, $id)
    {
        $pattern = Pattern::findOrFail($id);
        
        $pattern->update([
            'name' => $request->name,
            'description' => $request->description,
            'grid' => json_encode($request->grid),
        ]);

        return response()->json($pattern);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $pattern = Pattern::findOrFail($id);
        
        // Check if pattern is in use
        if ($pattern->rounds()->count() > 0 || $pattern->bets()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete pattern as it is in use by rounds or bets',
            ], 422);
        }
        
        $pattern->delete();

        return response()->json(['message' => 'Pattern deleted successfully']);
    }
}
