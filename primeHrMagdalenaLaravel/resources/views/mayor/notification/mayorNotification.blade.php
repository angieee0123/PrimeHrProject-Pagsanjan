{{-- The mayor's bell.

     The mayor had no notification panel at all: the markup was pasted into an
     admin copy and an employee copy, and the third area was simply left out,
     so nothing in the system could tell the mayor anything. The oversight
     audience carries decisions — leave, travel orders and pass slips that HR
     has settled — never queued work, because the mayor's area is read-only and
     a pending item in this list would imply an action these screens do not
     offer. --}}
@include('partials.notificationPanel', ['audience' => 'mayor'])
