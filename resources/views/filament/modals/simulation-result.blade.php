<div style="display: flex; flex-direction: column; gap: 16px; font-size: 14px;">
    {{-- Riepilogo --}}
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Righe totali</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->totalRows }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Righe valide</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #16a34a;">{{ $result->validRows }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Righe scartate</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #dc2626;">{{ $result->skippedRows }}</td>
            </tr>
        </tbody>
    </table>

    <hr style="border: none; border-top: 2px solid #e5e7eb; margin: 0;">

    {{-- Video --}}
    <div style="font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">Video</div>
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Dinamici</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->dynamicVideos }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Statici</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->staticVideos }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Combinazioni uniche</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->uniqueCombinations }}</td>
            </tr>
        </tbody>
    </table>

    <hr style="border: none; border-top: 2px solid #e5e7eb; margin: 0;">

    {{-- Canali --}}
    <div style="font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">Canali</div>
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Email</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->emailCount }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">SMS</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->smsCount }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Allegati</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $result->attachmentCount }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Segmenti mancanti --}}
    @if($result->missingSegmentsCount > 0)
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; color: #dc2626; font-weight: 600;">
            {{ $result->missingSegmentsCount }} segmenti video non trovati su disco
        </div>
    @endif

    {{-- Dettaglio scarti --}}
    @if(count($result->skippedByType) > 0)
        <hr style="border: none; border-top: 2px solid #e5e7eb; margin: 0;">
        <div style="font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">Dettaglio scarti</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                @foreach($result->skippedByType as $type => $count)
                    <tr style="{{ !$loop->last ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                        <td style="padding: 8px 0; color: #6b7280;">
                            @switch($type)
                                @case('missing_contact') Email e telefono mancanti @break
                                @case('missing_offer_code') Codice offerta non trovato @break
                                @case('missing_attachment') Allegato mancante @break
                                @default {{ $type }}
                            @endswitch
                        </td>
                        <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #dc2626;">{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Codici offerta non trovati --}}
    @if(count($result->missingOfferCodes) > 0)
        <hr style="border: none; border-top: 2px solid #e5e7eb; margin: 0;">
        <div style="font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">Codici offerta non trovati</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                @foreach($result->missingOfferCodes as $code => $count)
                    <tr style="{{ !$loop->last ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                        <td style="padding: 8px 0; color: #dc2626; font-family: monospace;">{{ $code }}</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #dc2626;">{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- File processati --}}
    @if(count($result->filesProcessed) > 0)
        <hr style="border: none; border-top: 2px solid #e5e7eb; margin: 0;">
        <div style="font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">File analizzati</div>
        <div style="color: #6b7280;">
            {{ implode(', ', $result->filesProcessed) }}
        </div>
    @endif
</div>
