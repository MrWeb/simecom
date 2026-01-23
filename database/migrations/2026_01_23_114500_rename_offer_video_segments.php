<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mappatura per LUCE
        $luceMappings = [
            'offerta-1' => 'offerta-sos-bee',
            'offerta-2' => 'offerta-easy-click',
            'offerta-3' => 'offerta-prezzo-chiaro',
            'offerta-4' => 'offerta-zero-rischi',
            'offerta-5' => 'offerta-led-collection',
            'offerta-6' => 'offerta-seguimi',
            'offerta-7' => 'offerta-seconda-casa',
        ];

        // Mappatura per GAS
        $gasMappings = [
            'offerta-1' => 'offerta-sos-bee',
            'offerta-2' => 'offerta-easy-click',
            'offerta-3' => 'offerta-green-planet',
            'offerta-4' => 'offerta-zero-rischi',
            'offerta-5' => 'offerta-turbo-green',
            'offerta-6' => 'offerta-dinamica',
            'offerta-7' => 'offerta-seconda-casa',
        ];

        // Aggiorna offer_codes per LUCE
        foreach ($luceMappings as $old => $new) {
            DB::table('offer_codes')
                ->where('type', 'luce')
                ->where('video_segment', $old)
                ->update(['video_segment' => $new]);
        }

        // Aggiorna offer_codes per GAS
        foreach ($gasMappings as $old => $new) {
            DB::table('offer_codes')
                ->where('type', 'gas')
                ->where('video_segment', $old)
                ->update(['video_segment' => $new]);
        }

        // Aggiorna video_campaigns - usa JSON_REPLACE per aggiornare i valori nell'array video_combination
        // Prima recupera i record e aggiorna manualmente
        $campaigns = DB::table('video_campaigns')->get();

        foreach ($campaigns as $campaign) {
            $combination = json_decode($campaign->video_combination, true);

            if (!is_array($combination)) {
                continue;
            }

            $mappings = $campaign->video_type === 'gas' ? $gasMappings : $luceMappings;
            $updated = false;

            foreach ($combination as $key => $value) {
                if (isset($mappings[$value])) {
                    $combination[$key] = $mappings[$value];
                    $updated = true;
                }
            }

            if ($updated) {
                DB::table('video_campaigns')
                    ->where('id', $campaign->id)
                    ->update(['video_combination' => json_encode($combination)]);
            }
        }
    }

    public function down(): void
    {
        // Mappatura inversa per LUCE
        $luceMappings = [
            'offerta-sos-bee' => 'offerta-1',
            'offerta-easy-click' => 'offerta-2',
            'offerta-prezzo-chiaro' => 'offerta-3',
            'offerta-zero-rischi' => 'offerta-4',
            'offerta-led-collection' => 'offerta-5',
            'offerta-seguimi' => 'offerta-6',
            'offerta-seconda-casa' => 'offerta-7',
        ];

        // Mappatura inversa per GAS
        $gasMappings = [
            'offerta-sos-bee' => 'offerta-1',
            'offerta-easy-click' => 'offerta-2',
            'offerta-green-planet' => 'offerta-3',
            'offerta-zero-rischi' => 'offerta-4',
            'offerta-turbo-green' => 'offerta-5',
            'offerta-dinamica' => 'offerta-6',
            'offerta-seconda-casa' => 'offerta-7',
        ];

        // Rollback offer_codes per LUCE
        foreach ($luceMappings as $new => $old) {
            DB::table('offer_codes')
                ->where('type', 'luce')
                ->where('video_segment', $new)
                ->update(['video_segment' => $old]);
        }

        // Rollback offer_codes per GAS
        foreach ($gasMappings as $new => $old) {
            DB::table('offer_codes')
                ->where('type', 'gas')
                ->where('video_segment', $new)
                ->update(['video_segment' => $old]);
        }

        // Rollback video_campaigns
        $campaigns = DB::table('video_campaigns')->get();

        foreach ($campaigns as $campaign) {
            $combination = json_decode($campaign->video_combination, true);

            if (!is_array($combination)) {
                continue;
            }

            $mappings = $campaign->video_type === 'gas' ? $gasMappings : $luceMappings;
            $updated = false;

            foreach ($combination as $key => $value) {
                if (isset($mappings[$value])) {
                    $combination[$key] = $mappings[$value];
                    $updated = true;
                }
            }

            if ($updated) {
                DB::table('video_campaigns')
                    ->where('id', $campaign->id)
                    ->update(['video_combination' => json_encode($combination)]);
            }
        }
    }
};
