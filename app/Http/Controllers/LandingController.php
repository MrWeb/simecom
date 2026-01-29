<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\VideoCampaign;

class LandingController extends Controller
{
    public function show(string $uuid)
    {
        $campaign = VideoCampaign::where('uuid', $uuid)->firstOrFail();

        // Traccia apertura
        $campaign->markAsOpened();

        if ($campaign->video_status !== 'ready') {
            return view('landing.not-ready', [
                'campaign' => $campaign,
            ]);
        }

        // Prendi il link di redirect in base al tipo (luce/gas)
        $redirectLink = Setting::get($campaign->video_type . '-link', 'https://simecom.it');

        return view('landing.video', [
            'campaign' => $campaign,
            'videoUrl' => $campaign->getVideoUrl(),
            'redirectLink' => $redirectLink,
            'attachmentUrl' => $campaign->getAttachmentUrl(),
        ]);
    }
}
