<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // All Product
       $products = Product::all();

       // Return Json Response
       return response()->json([
          'products' => $products
       ],200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:258',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string'
        ]);

        try {
            $imageName = Str::random(16).".".$request->image->getClientOriginalExtension();


            // Create Product
            Product::create([
                'name' => $request->name,
                'image' => $imageName,
                'description' => $request->description
            ]);


            // Save Image in Storage folder
             Storage::disk('public')->put($imageName, file_get_contents($request->image));


             return Storage::url($imageName);
             //return $imagePath = asset('storage/' . $imageName);

            // Return Json Response
            return response()->json([
                'message' => "Product successfully created."
            ],200);
        } catch (\Exception $e) {
            // Return Json Response
            return response()->json([
                'message' => "Something went really wrong!"
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         // Product Detail
       $product = Product::find($id);
       if(!$product){
         return response()->json([
            'message'=>'Product Not Found.'
         ],404);
       }

       // Return Json Response
       return response()->json([
          'product' => $product
       ],200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Find product
            $product = Product::find($id);
            if(!$product){
              return response()->json([
                'message'=>'Product Not Found.'
              ],404);
            }

            //echo "request : $request->image";
            $product->name = $request->name;
            $product->description = $request->description;

            if($request->image) {

                // Public storage
                $storage = Storage::disk('public');

                // Old iamge delete
                if($storage->exists($product->image))
                    $storage->delete($product->image);

                // Image name
                $imageName = Str::random(16).".".$request->image->getClientOriginalExtension();
                $product->image = $imageName;

                // Image save in public folder
                $storage->put($imageName, file_get_contents($request->image));
            }

            // Update Product
            $product->save();

            // Return Json Response
            return response()->json([
                'message' => "Product successfully updated."
            ],200);
        } catch (\Exception $e) {
            // Return Json Response
            return response()->json([
                'message' => "Something went really wrong!"
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Detail
        $product = Product::find($id);
        if(!$product){
          return response()->json([
             'message'=>'Product Not Found.'
          ],404);
        }

        // Public storage
        $storage = Storage::disk('public');

        // Iamge delete
        if($storage->exists($product->image))
            $storage->delete($product->image);

        // Delete Product
        $product->delete();

        // Return Json Response
        return response()->json([
            'message' => "Product successfully deleted."
        ],200);
    }

}
