<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VideoSegment extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'type',
        'filename',
        'is_offer',
        'active',
    ];

    protected $casts = [
        'is_offer' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Normalizza il filename per salvare solo il nome del file (senza path).
     */
    protected function filename(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn ($value) => $value ? basename($value) : $value,
        );
    }

    /**
     * Ritorna il path completo del file video.
     */
    public function getFilePath(): string
    {
        return "videos/{$this->type}/{$this->filename}";
    }

    /**
     * Ritorna il path assoluto del file video.
     */
    public function getAbsolutePath(): string
    {
        return storage_path("app/public/{$this->getFilePath()}");
    }

    /**
     * Verifica se il file video esiste.
     */
    public function fileExists(): bool
    {
        return Storage::disk('public')->exists($this->getFilePath());
    }

    /**
     * Ritorna l'URL pubblico del video.
     */
    public function getUrl(): string
    {
        return Storage::disk('public')->url($this->getFilePath());
    }

    /**
     * Scope per filtrare per tipo.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope per filtrare solo offerte attive.
     */
    public function scopeActiveOffers($query)
    {
        return $query->where('is_offer', true)->where('active', true);
    }

    /**
     * Ritorna le opzioni per il select raggruppate per tipo.
     */
    public static function getSelectOptions(?string $type = null): array
    {
        $query = self::activeOffers()->orderBy('name');

        if ($type) {
            $query->ofType($type);
            return $query->pluck('name', 'slug')->toArray();
        }

        // Raggruppa per tipo
        $segments = $query->get();

        return [
            'Luce' => $segments->where('type', 'luce')->pluck('name', 'slug')->toArray(),
            'Gas' => $segments->where('type', 'gas')->pluck('name', 'slug')->toArray(),
        ];
    }
}
