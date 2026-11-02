<table class="inv-head">
    <tr>
        <td style="width: 58%;">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" alt="" class="inv-logo">
            @endif
            @if(!empty($company['legal_name']))
                <div class="inv-company">{{ $company['legal_name'] }}</div>
            @endif
            @if($contactLines !== [])
                <table class="inv-contact">
                    @foreach($contactLines as $line)
                        <tr>
                            <td class="inv-ico-cell">
                                @if(!empty($line['icon']))
                                    <img src="{{ $line['icon'] }}" alt="" class="inv-ico">
                                @endif
                            </td>
                            <td class="inv-contact-text">{!! nl2br(e($line['text'])) !!}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td>
        <td style="width: 42%;">
            <div @class(['inv-title', 'inv-title-long' => mb_strlen($title) > 8])>{{ $title }}</div>
            @if(!empty($generatedLabel))
                <div class="inv-generated">{{ __('documents.labels.generated') }}: {{ $generatedLabel }}</div>
            @endif
            @if(($payload['copy_count'] ?? 1) > 1 && !empty($payload['subtitle']))
                <div class="inv-copy">{{ $payload['subtitle'] }}</div>
            @endif
        </td>
    </tr>
</table>
