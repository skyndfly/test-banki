<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ImageController extends Controller
{
    public function showUploadForm(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('images.index');
    }

    public function upload(Request $request): RedirectResponse
    {
        $uploadedImages = $request->file('images');
        if (count($uploadedImages) > Image::DOWNLOAD_IMAGE_COUNT) {
            return redirect()->back()->with('error', 'Запрещенно загружат больше 5 файлов одновременно.');
        }
        $imagesData = [];

        foreach ($uploadedImages as $image) {
            $originalName = $image->getClientOriginalName();
            $extension = $image->getClientOriginalExtension();
            $filename = Str::lower(Str::slug(pathinfo($originalName, PATHINFO_FILENAME))) . '.' . $extension;

            // Проверка на уникальность имени файла в базе данных
            if (Image::where('filename', $filename)->exists()) {
                $uuid = Str::uuid();
                $filename = Str::lower(Str::slug(pathinfo($originalName, PATHINFO_FILENAME))) . '_' . $uuid . '.' . $extension;
            }

            $image->move(public_path('uploads'), $filename);

            $imagesData[] = [
                'filename' => $filename,
                'created_at' => now()
            ];
        }

        Image::insert($imagesData);
        return redirect()->back()->with('success', 'Файлы успешно загруженны.');
    }

    public function list(Request $request)
    {
        $sort = $request->input('sort', 'filename');
        $direction = $request->input('direction', 'asc');

        $images = Image::orderBy($sort, $direction)->get();

        return view('images.list', compact('images', 'sort', 'direction'));
    }

    public function download(): BinaryFileResponse
    {
        $images = Image::orderBy('filename')->get();
        $zipName = 'images.zip';
        $zipPath = public_path($zipName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach ($images as $image) {
                $filePath = public_path('uploads/' . $image->filename);
                $zip->addFile($filePath, $image->filename);
            }
            $zip->close();
        }
        return response()->download($zipPath, $zipName)->deleteFileAfterSend();
    }

    public function actionGetList(): JsonResponse
    {
        $images = Image::get();

        return response()->json($images);
    }

    public function actionView(int $id): JsonResponse
    {
        try {
            $image = Image::findOrFail($id);
            return response()->json($image);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Image not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
