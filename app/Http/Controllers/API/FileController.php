<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\File;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/file/upload",
     *     tags={"File"},
     *     summary="Upload a file",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,bmp|max:2048'
        ]);
        $file = $request->file('file');
        $filePath = $file->store('pet-shop', 'public');
        $fileUuid = (string) Str::uuid();

        $fileNameWithExtension = $file->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

        $fileRecord = File::create([
            'uuid' => $fileUuid,
            'name' => $fileName,
            'path' => $filePath,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ]);

        return response()->json([
            "success" => 1,
            "data" => [
                "uuid" => $fileRecord->uuid,
                "name" => $fileRecord->name,
                "path" => Storage::url($fileRecord->path),
                "size" => $this->formatSizeUnits($fileRecord->size),
                "type" => $fileRecord->type,
                "updated_at" => $fileRecord->updated_at,
                "created_at" => $fileRecord->created_at
            ],
            "error" => null,
            "errors" => [],
            "extra" => []
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/file/{uuid}",
     *     tags={"File"},
     *     summary="Read a file",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         ),
     *         description="UUID of the file"
     *     ),
     *     @OA\Response(response="201", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function downloadFile($uuid)
    {
        $fileRecord = File::where('uuid', $uuid)->first();

        if (!$fileRecord) {
            return response()->json([
            "success" => 0,
            "data" => null,
            "error" => "File not found",
            "errors" => [],
            "extra" => []
            ], 404);
        }

        $filePath = storage_path('app/public/' . $fileRecord->path);

        if (!file_exists($filePath)) {
            return response()->json([
            "success" => 0,
            "data" => null,
            "error" => "File not found on server",
            "errors" => [],
            "extra" => []
            ], 404);
        }

        return response()->download($filePath, $fileRecord->name . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
