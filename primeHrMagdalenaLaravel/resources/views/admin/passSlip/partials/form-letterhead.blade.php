{{-- HRMO letterhead, placed at the coordinates the caller passes: the source
     document puts the masthead at 459.8 x 80.5 pt and the tourism mark at
     51.5 x 73 pt, at a slightly different offset in each of the two boxes.

     Preferred path is the office's own artwork. The drawn fallback exists so
     the form still prints on an install where those images were never copied
     across; it fills the same rectangle, but it is a likeness, not the
     letterhead. --}}
@if($letterhead['masthead'])
  <img src="{{ $letterhead['masthead'] }}" alt="" class="ps-img"
       style="left:{{ $mhLeft }}pt; top:{{ $mhTop }}pt; width:459.8pt; height:80.5pt">
  <img src="{{ $letterhead['mark'] }}" alt="" class="ps-img"
       style="left:{{ $mkLeft }}pt; top:{{ $mkTop }}pt; width:51.5pt; height:73pt">
@else
  <div class="lh-drawn" style="left:{{ $mhLeft }}pt; top:{{ $mhTop }}pt; width:459.8pt; height:80.5pt">
    <table class="lh-drawn-table">
      <tr>
        <td class="lh-seal">
          @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="" class="lh-seal-img">
          @endif
        </td>
        <td class="lh-text">
          <div class="lh-script lh-republic">{{ $letterhead['republic'] }}</div>
          <div class="lh-script lh-municipality">{{ $letterhead['municipality'] }}</div>
          <div class="lh-script lh-tagline">{{ $letterhead['tagline'] }}</div>
          <div class="lh-rule"></div>
          <div class="lh-office">{{ $letterhead['office'] }}</div>
          <div class="lh-tel">{{ $letterhead['telephone'] }}</div>
        </td>
        <td class="lh-emblems">
          @foreach($letterhead['emblems'] as $emblem)
            <img src="{{ $emblem }}" alt="" class="lh-emblem-img">
          @endforeach
        </td>
      </tr>
    </table>
  </div>
@endif
