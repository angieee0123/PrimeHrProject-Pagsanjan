{{--
    Pass slip — approve / disapprove confirmation.

    Replaces `confirm('Approve this pass slip?')` on the approve button and a
    bare `prompt('Reason for disapproval:')` on the disapprove one. The same
    three problems the travel order decision modal was built for apply here:

    · **Neither one said which slip.** The pending table can hold dozens of
      rows and both actions sit behind an ellipsis menu, so "Approve this pass
      slip?" is a question the approver answers about whichever row they
      *believe* they clicked. This dialog is built from that row — the
      employee, the slip number, the date, the time-out/time-in window and the
      reason — so the decision names what is being decided.
    · **Approving a pass slip is an attendance decision, and which one depends
      on the type.** `PassSlipComplianceService` excuses an approved *Official
      Activity* (CSC MC 21, s. 1991 counts time on official business as time
      rendered, so those minutes stop being undertime) but *charges* an
      approved *Personal Reason* as undertime, which then feeds the late/
      undertime deductions against VL then SL. Approving both from one
      "Are you sure?" hides the fact that the two do opposite things to pay.
    · **`prompt()` is not a form field.** `PassSlipController::disapprove()`
      validates `required|string|max:500`; prompt() enforced neither, had no
      label, and a stray Escape silently abandoned a refusal already decided
      on. The reason is also what the employee reads back on their own slip
      (`viewPassSlipModal`'s REMARKS block), so it is written once, here.

    One modal serves both decisions rather than two near-identical copies: the
    slip summary is identical either way, and only the accent, the wording and
    the reason field differ. `openPassSlipDecision()` in passSlipDecisionModal.js
    fills it in from the menu item that was pressed.
--}}
<x-modal id="passSlipDecisionModal" close="closePassSlipDecision" max-width="540px">
    <div class="psd-head">
        <div class="psd-icon" id="psdIcon">
            {{-- Swapped for the disapprove glyph by JS. --}}
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <span class="psd-eyebrow" id="psdEyebrow">PASS SLIP</span>
        <h3 class="psd-title" id="psdTitle">-</h3>
        <p class="psd-lede" id="psdLede">-</p>
    </div>

    <div class="modal-body psd-body">
        {{-- Whose slip this is. The face is what makes the row the approver
             clicked and the person they are deciding about read as the same
             one. --}}
        <div class="psd-filer">
            <span class="psd-avatar" id="psdAvatar">--</span>
            <div class="psd-filer-text">
                <p class="psd-filer-name" id="psdEmployee">-</p>
                <p class="psd-filer-role" id="psdEmployeeMeta">-</p>
            </div>
            <span class="psd-slip-no" id="psdSlipNumber">-</span>
        </div>

        {{-- What is being decided. The date leads: it is the day whose
             accredited hours this decision changes. --}}
        <div class="psd-slip">
            <div class="psd-slip-row">
                <span class="psd-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Date
                </span>
                <strong class="psd-slip-value" id="psdDate">-</strong>
            </div>
            <div class="psd-slip-row">
                <span class="psd-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Out · in
                </span>
                <strong class="psd-slip-value" id="psdWindow">-</strong>
            </div>
            <div class="psd-slip-row">
                <span class="psd-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Type · time away
                </span>
                <strong class="psd-slip-value" id="psdType">-</strong>
            </div>
            <div class="psd-slip-row">
                <span class="psd-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Destination
                </span>
                <strong class="psd-slip-value" id="psdDestination">-</strong>
            </div>
            <div class="psd-slip-row psd-slip-reason">
                <span class="psd-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Reason given
                </span>
                <span class="psd-slip-reason-text" id="psdReason">-</span>
            </div>
        </div>

        {{-- Disapprove only: the reason, as a real field carrying the
             controller's own `required|string|max:500`. --}}
        <div class="psd-note" id="psdNoteBlock" hidden>
            <label class="psd-note-label" for="psdNote">
                Why is this being disapproved? <span class="psd-required">Required</span>
            </label>
            <textarea id="psdNote" class="psd-note-input" rows="3" maxlength="500"
                      placeholder="e.g. The errand can be done during the lunch break — no office time needs to be missed."></textarea>
            <div class="psd-note-foot">
                <span id="psdNoteHint">The employee reads this on their own copy of the slip.</span>
                <span id="psdNoteCount">0 / 500</span>
            </div>
        </div>

        <p class="psd-consequence" id="psdConsequence">-</p>
    </div>

    <div class="modal-footer psd-footer">
        <button type="button" class="modal-btn-ghost" id="psdCancel" onclick="closePassSlipDecision()">Go back</button>
        <button type="button" class="modal-btn-primary" id="psdConfirm" onclick="submitPassSlipDecision()">
            <span id="psdConfirmLabel">Confirm</span>
        </button>
    </div>
</x-modal>

@push('scripts')
    @vite('resources/js/admin/passSlip/passSlipDecisionModal.js')
@endpush
