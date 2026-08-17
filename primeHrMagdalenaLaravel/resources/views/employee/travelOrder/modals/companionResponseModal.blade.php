{{--
    Companion invitation — accept / decline confirmation.

    Replaces a browser `confirm("Are you sure you want to accept this companion
    request?")` followed, on decline, by a `prompt()` for the reason. Two
    problems with that pair, and this modal exists to fix both:

    · **It never said which trip.** An employee can hold several invitations at
      once; a dialog that names no destination, no dates and no colleague is one
      the reader has to answer from memory. This one is built from the row that
      was clicked — "Travel with Maria Santos to Lucena City?" — and shows the
      dates being committed to before the answer is given.
    · **`prompt()` is not a form field.** It has no character limit against the
      server's `max:300`, no label, no styling, and cancelling it silently
      abandoned a decline the employee had already confirmed.

    One modal serves both answers rather than two near-identical copies: the
    trip summary is the same either way, and only the accent, the wording and
    the note field differ. `openCompanionResponse()` in employeeTravelOrder.js
    fills it in.
--}}
<x-modal id="companionResponseModal" close="closeCompanionResponse" max-width="520px">
    <div class="cr-head">
        <div class="cr-icon" id="crIcon">
            {{-- Swapped for the decline glyph by JS. --}}
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <span class="cr-eyebrow" id="crEyebrow">COMPANION REQUEST</span>
        <h3 class="cr-title" id="crTitle">-</h3>
        <p class="cr-lede" id="crLede">-</p>
    </div>

    <div class="modal-body cr-body">
        {{-- Who is asking. The invitation is from a colleague, not from the
             system, and the face is what makes that read as a person. --}}
        <div class="cr-filer">
            <span class="cr-avatar" id="crFilerAvatar">--</span>
            <div class="cr-filer-text">
                <p class="cr-filer-name" id="crFilerName">-</p>
                <p class="cr-filer-role">Filed this travel order · <span id="crOrderNumber">-</span></p>
            </div>
        </div>

        {{-- What is being agreed to. Dates first: that is the part of a travel
             order that collides with the rest of somebody's month. --}}
        <div class="cr-trip">
            <div class="cr-trip-row">
                <span class="cr-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Travel dates
                </span>
                <strong class="cr-trip-value" id="crDates">-</strong>
            </div>
            <div class="cr-trip-row">
                <span class="cr-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Destination
                </span>
                <strong class="cr-trip-value" id="crDestination">-</strong>
            </div>
            <div class="cr-trip-row">
                <span class="cr-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    Travelling party
                </span>
                <strong class="cr-trip-value" id="crParty">-</strong>
            </div>
            <div class="cr-trip-row cr-trip-purpose">
                <span class="cr-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Purpose
                </span>
                <span class="cr-trip-purpose-text" id="crPurpose">-</span>
            </div>
        </div>

        {{-- Decline only: the reason, as a real field with the server's own
             300-character limit rather than an unbounded prompt(). --}}
        <div class="cr-note" id="crNoteBlock" hidden>
            <label class="cr-note-label" for="crNote">
                Let <span id="crNoteWho">the filer</span> know why <span class="cr-optional">optional</span>
            </label>
            <textarea id="crNote" class="cr-note-input" rows="3" maxlength="300"
                      placeholder="e.g. I'm on leave that week, or already booked on another travel order."></textarea>
            <div class="cr-note-foot">
                <span id="crNoteHint">This is sent with your response.</span>
                <span id="crNoteCount">0 / 300</span>
            </div>
        </div>

        <p class="cr-consequence" id="crConsequence">-</p>
    </div>

    <div class="modal-footer cr-footer">
        <button type="button" class="modal-btn-ghost" id="crCancel" onclick="closeCompanionResponse()">Go back</button>
        <button type="button" class="modal-btn-primary" id="crConfirm" onclick="submitCompanionResponse()">
            <span id="crConfirmLabel">Confirm</span>
        </button>
    </div>
</x-modal>
