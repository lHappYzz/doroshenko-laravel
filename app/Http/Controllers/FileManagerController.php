<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexFileManagerRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Http\Resources\FileCollection;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends Controller
{
    /**
     * @param IndexFileManagerRequest $request
     * @return FileCollection
     */
    public function index(IndexFileManagerRequest $request): FileCollection
    {
        if ($request->has('sort_field')) {
            $query = File::query()->orderBy($request->get('sort_field'), $request->get('sort_order'));
        } else {
            $query = File::query();
        }

        return new FileCollection($query->paginate(10));
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse|FileResource
     */
    public function get(Request $request, string $id): JsonResponse | FileResource
    {
        try {
            return new FileResource(File::query()->findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "File with given id ($id) not found"
            ], 404);
        }
    }

    /**
     * @param UploadFileRequest $request
     * @return JsonResponse
     */
    public function upload(UploadFileRequest $request): JsonResponse
    {
        $paths = DB::transaction(function () use ($request) {
            $paths = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('', 'fileManager');

                $fileRecord = File::query()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ]);

                $paths[] = $fileRecord;
            }

            return $paths;
        });

        return response()->json([
            'message' => 'Files uploaded successfully',
            'files' => $paths
        ], 201);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return StreamedResponse|JsonResponse
     */
    public function download(Request $request, string $id): StreamedResponse | JsonResponse
    {
        try {
            $file = File::query()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "File with given id ($id) not found",
            ]);
        }

        return Storage::disk('fileManager')->download($file->path, $file->name, [
            'Content-Disposition' => "attachment; filename*=UTF-8''" . rawurlencode($file->name)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $file = File::query()->findOrFail($request->get('id'));

        Storage::disk('fileManager')->delete($file->path);

        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * @param UpdateFileRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateFileRequest $request, string $id): JsonResponse
    {
        $file = File::query()->findOrFail($id);

        $file->update($request->validated());

        return response()->json([
            'message' => 'File updated successfully',
            'file' => $file,
        ]);
    }
}
