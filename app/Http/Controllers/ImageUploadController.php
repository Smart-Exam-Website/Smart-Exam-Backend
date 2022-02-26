<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{

    
    
    public function imageUploadPost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $imageName = time().'.'.$request->image->extension();  
     
        $path = Storage::disk('s3')->put('uploads', $request->image);
        $path = Storage::disk('s3')->url($path);
    
        return response()->json(['message' => 'success ,You have successfully upload image.', 'image' => $imageName]);
    }
}
