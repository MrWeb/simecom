<?php

namespace Database\Seeders;

use App\Models\OfferCode;
use Illuminate\Database\Seeder;

class OfferteAttiveSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            // ==================== ENERGIA ELETTRICA (EE) ====================
            ['code' => 'NV2601EDRSMI3F', 'offer_name' => 'SMILE - Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EDRPCF3F260101', 'offer_name' => 'PLACET FIX - Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EVRPCF3F260101', 'offer_name' => 'PLACET FIX - P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EDRPCV3F260101', 'offer_name' => 'PLACET VAR - Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EVRPCV3F260101', 'offer_name' => 'PLACET VAR - P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDWPUN3FA', 'offer_name' => 'EASY CLICK (W) - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVWPUN3FA', 'offer_name' => 'EASY CLICK (W) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDRPUN3FA', 'offer_name' => 'SEGUIMI! - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVRPUN3FA', 'offer_name' => 'SEGUIMI! - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDJLED3FA', 'offer_name' => 'LED COLLECTION - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDRZRS3FA', 'offer_name' => 'ZERO RISCHI - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDSPUN3FA', 'offer_name' => 'SOSbee - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVKPUN3FA', 'offer_name' => 'BIG STAR_A (EVK) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDMPUN3FD', 'offer_name' => 'MENO 40 - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EDRFIX3F260101A', 'offer_name' => 'PREZZO CHIARO - Fix Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EVRFIX3F260101A', 'offer_name' => 'PREZZO CHIARO - Fix P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EDBFIX3F260101A', 'offer_name' => 'FACILE (B) - Fix Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EVBFIX3F260101A', 'offer_name' => 'FACILE (B) - Fix P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EDXFIX3F260101A', 'offer_name' => 'FACILE + (X) - Fix Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'EVXFIX3F260101A', 'offer_name' => 'FACILE + (X) - Fix P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDNPUN3FA', 'offer_name' => 'DOMINA - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVNPUN3FA', 'offer_name' => 'DOMINA - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDBPUN3FA', 'offer_name' => 'FACILE (B) - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVBPUN3FA', 'offer_name' => 'FACILE (B) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDXPUN3FA', 'offer_name' => 'FACILE+ (X) - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVXPUN3FA', 'offer_name' => 'FACILE+ (X) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDDPUN3FA', 'offer_name' => 'SEMPLICE (D) - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVDPUN3FA', 'offer_name' => 'SEMPLICE A (D) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVDPUN3FB', 'offer_name' => 'SEMPLICE B (D) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDAPUN3FA', 'offer_name' => 'SEMPLICE+ (A) - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVAPUN3FA', 'offer_name' => 'SEMPLICE+ (A) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EDLPUN3FA', 'offer_name' => 'RELAX (L) - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVTPUN3FB', 'offer_name' => 'AGILE (T) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVZPUN3F2P', 'offer_name' => 'SPRINT 2P (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVZPUN3F5P', 'offer_name' => 'SPRINT 5P (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVZPUN3F6P', 'offer_name' => 'SPRINT 6P (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVZPUN3F7P', 'offer_name' => 'SPRINT 7P (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2508EVZPUN3F8P', 'offer_name' => 'SPRINT 8P (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2511EDSFOT3FA', 'offer_name' => 'ENERGIA MILLE - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2511EDCPUN3FB', 'offer_name' => '2^ CASA - Var Casa', 'video_segment' => null, 'type' => 'luce'],
            ['code' => 'NV2511EDEPUN3FA', 'offer_name' => 'ZERO SPREAD CLUB - Var Casa', 'video_segment' => null, 'type' => 'luce'],

            // ==================== GAS ====================
            ['code' => 'NV2501GDRSMI', 'offer_name' => 'SMILE - Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GDRPCV260101A', 'offer_name' => 'PLACET VAR (Ex Tutela) - Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GVRPCV260101A', 'offer_name' => 'PLACET VAR (Ex Tutela) - P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GDRPCF260101', 'offer_name' => 'PLACET FIX - Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GVRPCF260101', 'offer_name' => 'PLACET FIX - P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GDRPCV260101', 'offer_name' => 'PLACET VAR - Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GVRPCV260101', 'offer_name' => 'PLACET VAR - P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDWPSVA', 'offer_name' => 'EASY CLICK (W) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVWPSVA', 'offer_name' => 'EASY CLICK (W) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDRPSVA', 'offer_name' => 'DINAMICA - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVRPSVA', 'offer_name' => 'DINAMICA - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDRGREC', 'offer_name' => 'TURBO GREEN (C) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVRGREC', 'offer_name' => 'TURBO GREEN (C) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDRZRSA', 'offer_name' => 'ZERO RISCHI - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDSPSVA', 'offer_name' => 'SOS BEE - Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVKPSVA', 'offer_name' => 'BIG STAR (GVK) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDMPSVD', 'offer_name' => 'MENO 40 (D_C) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GDRFIX260101A', 'offer_name' => 'GREEN PLANET - Fix Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GVRFIX260101A', 'offer_name' => 'GREEN PLANET - Fix P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GDBFIX260101A', 'offer_name' => 'FACILE (B) - Fix Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GVBFIX260101A', 'offer_name' => 'FACILE (B) - Fix P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GDXFIX260101A', 'offer_name' => 'FACILE + (X) - Fix Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'GVXFIX260101A', 'offer_name' => 'FACILE + (X) - Fix P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDNPSVA', 'offer_name' => 'DOMINA - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVNPSVA', 'offer_name' => 'DOMINA - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDBPSVA', 'offer_name' => 'FACILE (B) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVBPSVA', 'offer_name' => 'FACILE (B) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDXPSVA', 'offer_name' => 'FACILE+ (X) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVXPSVA', 'offer_name' => 'FACILE+ (X) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDDPSVA', 'offer_name' => 'SEMPLICE (D) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVDPSVA', 'offer_name' => 'SEMPLICE (D) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDAPSVA', 'offer_name' => 'SEMPLICE+ (A) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVAPSVA', 'offer_name' => 'SEMPLICE+ (A) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GDLPSVA', 'offer_name' => 'RELAX (L) - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVZPSVB', 'offer_name' => 'SPRINT B (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVZPSVC', 'offer_name' => 'SPRINT C (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVZPSVG', 'offer_name' => 'SPRINT G (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2508GVZPSVH', 'offer_name' => 'SPRINT H (Z) - Var P.IVA', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2511GDCPSVB', 'offer_name' => '2^ CASA - Var Casa', 'video_segment' => null, 'type' => 'gas'],
            ['code' => 'NV2511GDEPSVA', 'offer_name' => 'ZERO SPREAD CLUB - Var Casa', 'video_segment' => null, 'type' => 'gas'],
        ];

        foreach ($offers as $offer) {
            OfferCode::updateOrCreate(
                ['code' => $offer['code']],
                $offer
            );
        }

        $this->command->info('Imported ' . count($offers) . ' offer codes from OfferteAttive.');
    }
}
