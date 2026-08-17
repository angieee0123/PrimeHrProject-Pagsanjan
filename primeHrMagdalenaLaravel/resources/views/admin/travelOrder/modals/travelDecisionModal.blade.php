{{--
    Travel order — approve / disapprove confirmation.

    Replaces `confirm('Approve this travel order?')` on the approve button and a
    bare `prompt('Reason for disapproval:')` on the disapprove one. Three
    problems with that pair, and this modal exists to fix all three:

    · **Neither one said which order.** The pending table can hold dozens of
      rows and the menu is opened from an ellipsis button; a dialog reading
      "Approve this travel order?" is one the approver answers from whichever
      row they *believe* they clicked. This one is built from that row — the
      employee, the order number, the destination, the dates and the party —
      so the decision names what is being decided.
    · **Approval is not a small yes.** It writes TRAVEL_ORDER attendance rows
      across every weekday of the trip (see TravelOrderObserver), which feed the
      DTR and payroll, and it notifies the employee. That consequence is stated
      before the button, not discovered afterwards.
    · **`prompt()` is not a form field.** The reason is `required|string|max:500`
      server-side; prompt() enforced neither, had no label, and a stray Escape
      silently abandoned a disapproval the approver had already decided on.

    One modal serves both decisions rather than two near-identical copies: the
    order summary is identical either way, and only the accent, the wording and
    the reason field differ. `openTravelDecision()` in travelDecisionModal.js
    fills it in from the button that was pressed.
--}}
<x-modal id="travelDecisionModal" close="closeTravelDecision" max-width="540px">
    <div class="tdm-head">
        <div class="tdm-icon" id="tdmIcon">
            {{-- Swapped for the disapprove glyph by JS. --}}
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <span class="tdm-eyebrow" id="tdmEyebrow">TRAVEL ORDER</span>
        <h3 class="tdm-title" id="tdmTitle">-</h3>
        <p class="tdm-lede" id="tdmLede">-</p>
    </div>

    <div class="modal-body tdm-body">
        {{-- Whose order this is. The face is what makes the row the approver
             clicked and the person they are deciding about read as the same
             one. --}}
        <div class="tdm-filer">
            <span class="tdm-avatar" id="tdmAvatar">--</span>
            <div class="tdm-filer-text">
                <p class="tdm-filer-name" id="tdmEmployee">-</p>
                <p class="tdm-filer-role" id="tdmEmployeeMeta">-</p>
            </div>
            <span class="tdm-order-no" id="tdmOrderNumber">-</span>
        </div>

        {{-- What is being decided. Dates first: they are what the approval
             writes onto the employee's DTR. --}}
        <div class="tdm-trip">
            <div class="tdm-trip-row">
                <span class="tdm-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Travel dates
                </span>
                <strong class="tdm-trip-value" id="tdmDates">-</strong>
            </div>
            <div class="tdm-trip-row">
                <span class="tdm-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Destination
                </span>
                <strong class="tdm-trip-value" id="tdmDestination">-</strong>
            </div>
            <div class="tdm-trip-row">
                <span class="tdm-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    Travelling party
                </span>
                <strong class="tdm-trip-value" id="tdmParty">-</strong>
            </div>
            <div class="tdm-trip-row">
                <span class="tdm-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Transport · budget
                </span>
                <strong class="tdm-trip-value" id="tdmTransport">-</strong>
            </div>
            <div class="tdm-trip-row tdm-trip-purpose">
                <span class="tdm-trip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Purpose
                </span>
                <span class="tdm-trip-purpose-text" id="tdmPurpose">-</span>
            </div>
        </div>

        {{-- Disapprove only: the reason, as a real field with the controller's
             own `required|string|max:500` rather than an unbounded prompt().
             The employee reads this on their travel order, so it is written
             once, here, rather than explained later. --}}
        <div class="tdm-note" id="tdmNoteBlock" hidden>
            <label class="tdm-note-label" for="tdmNote">
                Why is this being disapproved? <span class="tdm-required">Required</span>
            </label>
            <textarea id="tdmNote" class="tdm-note-input" rows="3" maxlength="500"
                      placeholder="e.g. The purpose is already covered by the regional office's trip on the same dates."></textarea>
            <div class="tdm-note-foot">
                <span id="tdmNoteHint">This is sent to the employee with the decision.</span>
                <span id="tdmNoteCount">0 / 500</span>
            </div>
        </div>

        <p class="tdm-consequence" id="tdmConsequence">-</p>
    </div>

    <div class="modal-footer tdm-footer">
        <button type="button" class="modal-btn-ghost" id="tdmCancel" onclick="closeTravelDecision()">Go back</button>
        <button type="button" class="modal-btn-primary" id="tdmConfirm" onclick="submitTravelDecision()">
            <span id="tdmConfirmLabel">Confirm</span>
        </button>
    </div>
</x-modal>

@push('scripts')
    @vite('resources/js/admin/travelOrder/travelDecisionModal.js')
@endpush
