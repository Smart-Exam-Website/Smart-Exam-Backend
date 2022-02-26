<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{



    /**
     * @OA\Post(
     *      path="/image-upload",
     *      operationId="imageUpload",
     *      tags={"Image"},
     *      summary="Upload image",
     *      description="Returns success message and image name",
     *      security={ {"bearer": {} }},
     *      @OA\RequestBody(
     *          required=true,_
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *  @OA\JsonContent(
     *              @OA\Property(property="image", type="string", example="image.jpg")
     *          ),
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */

    public function imageUploadPost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imageName = time() . '.' . $request->image->extension();

        $path = Storage::disk('s3')->put('uploads', $request->image);
        $path = Storage::disk('s3')->url($path);

        return response()->json(['message' => 'success ,You have successfully upload image.', 'image' => $imageName]);
    }
}
