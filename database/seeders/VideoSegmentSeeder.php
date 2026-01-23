<?php

namespace Database\Seeders;

use App\Models\VideoSegment;
use Illuminate\Database\Seeder;

class VideoSegmentSeeder extends Seeder
{
    public function run(): void
    {
        // Video LUCE
        $luceVideos = [
            ['slug' => 'offerta-sos-bee', 'name' => 'SOS Bee', 'filename' => 'offerta-sos-bee.mp4', 'is_offer' => true],
            ['slug' => 'offerta-easy-click', 'name' => 'Easy Click', 'filename' => 'offerta-easy-click.mp4', 'is_offer' => true],
            ['slug' => 'offerta-prezzo-chiaro', 'name' => 'Prezzo Chiaro', 'filename' => 'offerta-prezzo-chiaro.mp4', 'is_offer' => true],
            ['slug' => 'offerta-zero-rischi', 'name' => 'Zero Rischi', 'filename' => 'offerta-zero-rischi.mp4', 'is_offer' => true],
            ['slug' => 'offerta-led-collection', 'name' => 'Led Collection', 'filename' => 'offerta-led-collection.mp4', 'is_offer' => true],
            ['slug' => 'offerta-seguimi', 'name' => 'Seguimi', 'filename' => 'offerta-seguimi.mp4', 'is_offer' => true],
            ['slug' => 'offerta-seconda-casa', 'name' => 'Seconda Casa', 'filename' => 'offerta-seconda-casa.mp4', 'is_offer' => true],
            // Segmenti non-offerta
            ['slug' => 'benvenuto', 'name' => 'Benvenuto', 'filename' => 'benvenuto.mp4', 'is_offer' => false],
            ['slug' => 'bolletta-digitale', 'name' => 'Bolletta Digitale', 'filename' => 'bolletta-digitale.mp4', 'is_offer' => false],
            ['slug' => 'bolletta-digitale-2', 'name' => 'Bolletta Digitale 2', 'filename' => 'bolletta-digitale-2.mp4', 'is_offer' => false],
            ['slug' => 'porta-un-amico', 'name' => 'Porta un Amico', 'filename' => 'porta-un-amico.mp4', 'is_offer' => false],
            ['slug' => 'prodotti', 'name' => 'Prodotti', 'filename' => 'prodotti.mp4', 'is_offer' => false],
            ['slug' => 'fine-1', 'name' => 'Fine 1', 'filename' => 'fine-1.mp4', 'is_offer' => false],
            ['slug' => 'fine-2', 'name' => 'Fine 2', 'filename' => 'fine-2.mp4', 'is_offer' => false],
        ];

        foreach ($luceVideos as $video) {
            VideoSegment::updateOrCreate(
                ['slug' => $video['slug'], 'type' => 'luce'],
                array_merge($video, ['type' => 'luce', 'active' => true])
            );
        }

        // Video GAS
        $gasVideos = [
            ['slug' => 'offerta-sos-bee', 'name' => 'SOS Bee', 'filename' => 'offerta-sos-bee.mp4', 'is_offer' => true],
            ['slug' => 'offerta-easy-click', 'name' => 'Easy Click', 'filename' => 'offerta-easy-click.mp4', 'is_offer' => true],
            ['slug' => 'offerta-green-planet', 'name' => 'Green Planet', 'filename' => 'offerta-green-planet.mp4', 'is_offer' => true],
            ['slug' => 'offerta-zero-rischi', 'name' => 'Zero Rischi', 'filename' => 'offerta-zero-rischi.mp4', 'is_offer' => true],
            ['slug' => 'offerta-turbo-green', 'name' => 'Turbo Green', 'filename' => 'offerta-turbo-green.mp4', 'is_offer' => true],
            ['slug' => 'offerta-dinamica', 'name' => 'Dinamica', 'filename' => 'offerta-dinamica.mp4', 'is_offer' => true],
            ['slug' => 'offerta-seconda-casa', 'name' => 'Seconda Casa', 'filename' => 'offerta-seconda-casa.mp4', 'is_offer' => true],
            // Segmenti non-offerta
            ['slug' => 'benvenuto', 'name' => 'Benvenuto', 'filename' => 'benvenuto.mp4', 'is_offer' => false],
            ['slug' => 'bolletta-digitale', 'name' => 'Bolletta Digitale', 'filename' => 'bolletta-digitale.mp4', 'is_offer' => false],
            ['slug' => 'bolletta-digitale-2', 'name' => 'Bolletta Digitale 2', 'filename' => 'bolletta-digitale-2.mp4', 'is_offer' => false],
            ['slug' => 'porta-un-amico', 'name' => 'Porta un Amico', 'filename' => 'porta-un-amico.mp4', 'is_offer' => false],
            ['slug' => 'prodotti', 'name' => 'Prodotti', 'filename' => 'prodotti.mp4', 'is_offer' => false],
            ['slug' => 'fine-1', 'name' => 'Fine 1', 'filename' => 'fine-1.mp4', 'is_offer' => false],
            ['slug' => 'fine-2', 'name' => 'Fine 2', 'filename' => 'fine-2.mp4', 'is_offer' => false],
        ];

        foreach ($gasVideos as $video) {
            VideoSegment::updateOrCreate(
                ['slug' => $video['slug'], 'type' => 'gas'],
                array_merge($video, ['type' => 'gas', 'active' => true])
            );
        }

        $this->command->info('Imported ' . (count($luceVideos) + count($gasVideos)) . ' video segments.');
    }
}
