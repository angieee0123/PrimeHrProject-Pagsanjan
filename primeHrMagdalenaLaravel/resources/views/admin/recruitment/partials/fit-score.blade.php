{{--
    A Fit Score, with the band it falls into as its tooltip.

    The band is the same one ChartDataService's fit distribution draws, so a
    candidate marked "Strong" here is in the strong bar on the overview. Included
    with: ['score' => $row['fit_score']]
--}}
@php
    $score = $score ?? null;
    $band = \App\Services\HiringPresenter::fitBand($score);
@endphp

@if(is_numeric($score))
    <span class="hire-score" title="{{ $band['label'] }} fit">{{ number_format((float) $score, 1) }}<small>/100</small></span>
@else
    <span class="hire-muted" title="{{ $band['label'] }}">—</span>
@endif
