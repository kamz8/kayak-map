<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GpxUploadRequest;
use App\Jobs\ProcessGpxFileJob;
use App\Models\GpxProcessingStatus;
use Illuminate\Http\JsonResponse;

class GPXController extends Controller
{
    public function upload(GpxUploadRequest $request): JsonResponse
    {
        $file = $request->file('gpx_file');
        $trailId = $request->input('trail_id');

        // Zapisz plik tymczasowo
        $filePath = $file->store('gpx/temp');

        // Utwórz status przetwarzania
        $status = GpxProcessingStatus::create([
            'trail_id' => $trailId,
            'file_path' => $filePath,
            'status' => 'pending',
            'message' => 'Processing queued'
        ]);

        // Dispatch job
        ProcessGpxFileJob::dispatch($filePath, $trailId, $status->id);

        return response()->json([
            'message' => 'GPX file uploaded and queued for processing',
            'status_id' => $status->id
        ]);
    }

    public function status($statusId): JsonResponse
    {
        $status = GpxProcessingStatus::findOrFail($statusId);

        return response()->json($status);
    }
}
