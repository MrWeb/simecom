<?php

namespace Database\Seeders;

use App\Models\OfferCode;
use Illuminate\Database\Seeder;

class OfferCodeSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            // LUCE
            ['code' => 'NV2508EDSPUN3FA', 'offer_name' => 'SOS BEE CASA', 'video_segment' => 'offerta-sos-bee', 'type' => 'luce'],

            ['code' => 'NV2508EDWPUN3FA', 'offer_name' => 'EASY CLICK CASA', 'video_segment' => 'offerta-easy-click', 'type' => 'luce'],
            ['code' => 'NV2508EVWPUN3FA', 'offer_name' => 'EASY CLICK P.IVA', 'video_segment' => 'offerta-easy-click', 'type' => 'luce'],

            ['code' => 'EDRFIX3F251201A', 'offer_name' => 'PREZZO CHIARO CASA', 'video_segment' => 'offerta-prezzo-chiaro', 'type' => 'luce'],
            ['code' => 'EVRFIX3F251201A', 'offer_name' => 'PREZZO CHIARO P.IVA', 'video_segment' => 'offerta-prezzo-chiaro', 'type' => 'luce'],

            ['code' => 'NV2508EDRZRS3FA', 'offer_name' => 'ZERO RISCHI', 'video_segment' => 'offerta-zero-rischi', 'type' => 'luce'],

            ['code' => 'NV2508EDJLED3FA', 'offer_name' => 'LED COLLECTION', 'video_segment' => 'offerta-led-collection', 'type' => 'luce'],

            ['code' => 'NV2508EDRPUN3FA', 'offer_name' => 'SEGUIMI CASA', 'video_segment' => 'offerta-seguimi', 'type' => 'luce'],
            ['code' => 'NV2508EVRPUN3FA', 'offer_name' => 'SEGUIMI P.IVA', 'video_segment' => 'offerta-seguimi', 'type' => 'luce'],

            ['code' => 'NV2511EDCPUN3FB', 'offer_name' => 'SECONDA CASA', 'video_segment' => 'offerta-seconda-casa', 'type' => 'luce'],

            // GAS
            ['code' => 'NV2508GDSPSVA', 'offer_name' => 'SOS BEE', 'video_segment' => 'offerta-sos-bee', 'type' => 'gas'],

            ['code' => 'NV2508GDWPSVA', 'offer_name' => 'EASY CLICK CASA', 'video_segment' => 'offerta-easy-click', 'type' => 'gas'],
            ['code' => 'NV2508GVWPSVA', 'offer_name' => 'EASY CLICK P.IVA', 'video_segment' => 'offerta-easy-click', 'type' => 'gas'],

            ['code' => 'GDRFIX251201A', 'offer_name' => 'GREEN PLANET CASA', 'video_segment' => 'offerta-green-planet', 'type' => 'gas'],
            ['code' => 'GVRFIX251201A', 'offer_name' => 'GREEN PLANET P.IVA', 'video_segment' => 'offerta-green-planet', 'type' => 'gas'],

            ['code' => 'NV2508GDRZRSA', 'offer_name' => 'ZERO RISCHI', 'video_segment' => 'offerta-zero-rischi', 'type' => 'gas'],

            ['code' => 'NV2508GDRGREC', 'offer_name' => 'TURBO GREEN CASA', 'video_segment' => 'offerta-turbo-green', 'type' => 'gas'],
            ['code' => 'NV2508GVRGREC', 'offer_name' => 'TURBO GREEN P.IVA', 'video_segment' => 'offerta-turbo-green', 'type' => 'gas'],

            ['code' => 'NV2508GDRPSVA', 'offer_name' => 'DINAMICA CASA', 'video_segment' => 'offerta-dinamica', 'type' => 'gas'],
            ['code' => 'NV2508GVRPSVA', 'offer_name' => 'DINAMICA P.IVA', 'video_segment' => 'offerta-dinamica', 'type' => 'gas'],

            ['code' => 'NV2511GDCPSVA', 'offer_name' => 'SECONDA CASA GAS', 'video_segment' => 'offerta-seconda-casa', 'type' => 'gas'],
        ];

        foreach ($offers as $offer) {
            OfferCode::updateOrCreate(
                ['code' => $offer['code']],
                $offer
            );
        }

        $this->command->info('Imported ' . count($offers) . ' offer codes.');
    }
}
