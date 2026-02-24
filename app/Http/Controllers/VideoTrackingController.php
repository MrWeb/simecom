<?php

namespace App\Http\Controllers;

use App\Models\VideoCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoTrackingController extends Controller
{
    public function track(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'watched_seconds' => 'required|integer|min:0',
            'duration' => 'nullable|integer|min:0',
            'completed' => 'boolean',
        ]);

        $campaign = VideoCampaign::where('uuid', $uuid)->first();

        if (! $campaign) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = [];

        // Aggiorna solo se watched_seconds è maggiore del valore salvato (no regressione)
        if ($validated['watched_seconds'] > $campaign->video_watched_seconds) {
            $data['video_watched_seconds'] = $validated['watched_seconds'];
        }

        if (! empty($validated['duration']) && ! $campaign->video_duration) {
            $data['video_duration'] = $validated['duration'];
        }

        if (! empty($validated['completed']) && ! $campaign->video_completed) {
            $data['video_completed'] = true;
        }

        if (! empty($data)) {
            $campaign->update($data);
        }

        return response()->json(['ok' => true]);
    }
}
