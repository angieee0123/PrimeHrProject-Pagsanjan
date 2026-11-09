@extends('layouts.app')

{{--
    Create a vacancy.

    The form mirrors the vacancy contract rather than inventing its own shape:
    the five qualification criteria are the ones the Fit Score is weighted
    across, and the weights must total 100% — both are enforced by the controller
    and the score engine, and both are checked here first so the reader gets the
    message beside the field instead of a redirect back.

    Publishing (OPEN) and drafting (DRAFT) are the only two states a new position
    can have. Everything after that is a lifecycle move on the vacancy page.
--}}

@section('content')
@include('admin.topbar.recruitmentTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

    @include('admin.recruitment.partials.flash')

    <div class="hire-toolbar">
        <a href="{{ route('admin.recruitment.vacancies') }}" class="btn-ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            All vacancies
        </a>
    </div>

    <form method="POST" action="{{ route('admin.recruitment.store') }}">
        @csrf

        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">The position</h3>
                    <p class="table-sub">What is being hired for, and where</p>
                </div>
            </div>

            <div class="hire-section-body">
                <div class="hire-form-grid">
                    <div class="hire-field is-wide">
                        <label class="hire-label" for="position_title">Position title <span class="hire-required">*</span></label>
                        <input type="text" id="position_title" name="position_title" class="hire-input" required maxlength="255"
                               value="{{ old('position_title') }}" placeholder="Administrative Officer II">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="place_of_assignment">Place of assignment <span class="hire-required">*</span></label>
                        <input type="text" id="place_of_assignment" name="place_of_assignment" class="hire-input" required maxlength="255"
                               value="{{ old('place_of_assignment') }}" placeholder="Office of the Municipal Mayor">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="plantilla_item_no">Plantilla item number</label>
                        <input type="text" id="plantilla_item_no" name="plantilla_item_no" class="hire-input" maxlength="100"
                               value="{{ old('plantilla_item_no') }}">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="salary_grade">Salary grade</label>
                        <input type="number" id="salary_grade" name="salary_grade" class="hire-input" min="1" max="33"
                               value="{{ old('salary_grade') }}" placeholder="11">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="monthly_salary">Monthly salary <span class="hire-required">*</span></label>
                        <input type="number" id="monthly_salary" name="monthly_salary" class="hire-input" required
                               min="0" step="0.01" value="{{ old('monthly_salary') }}" placeholder="27000.00">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="slots">Authorized slots <span class="hire-required">*</span></label>
                        <input type="number" id="slots" name="slots" class="hire-input" required min="1" max="100"
                               value="{{ old('slots', 1) }}">
                        <p class="hire-hint">How many hires the position holds before it counts as filled.</p>
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="status">Initial status <span class="hire-required">*</span></label>
                        <select id="status" name="status" class="hire-select" required>
                            <option value="OPEN" @selected(old('status') === 'OPEN')>Publish now (Open)</option>
                            <option value="DRAFT" @selected(old('status') === 'DRAFT')>Save as draft</option>
                        </select>
                        <p class="hire-hint">A draft is never public and never lapses until it is published.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Qualification standards</h3>
                    <p class="table-sub">The CSC QS the Fit Score measures each applicant against</p>
                </div>
            </div>

            <div class="hire-section-body">
                <div class="hire-form-grid">
                    <div class="hire-field is-wide">
                        <label class="hire-label" for="qs_education">Education <span class="hire-required">*</span></label>
                        <input type="text" id="qs_education" name="qs_education" class="hire-input" required maxlength="255"
                               value="{{ old('qs_education') }}" placeholder="Bachelor's degree relevant to the job">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="qs_training_hours">Training hours <span class="hire-required">*</span></label>
                        <input type="number" id="qs_training_hours" name="qs_training_hours" class="hire-input" required min="0" max="100000"
                               value="{{ old('qs_training_hours', 0) }}">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="qs_experience_years">Experience years <span class="hire-required">*</span></label>
                        <input type="number" id="qs_experience_years" name="qs_experience_years" class="hire-input" required
                               min="0" max="60" step="0.5" value="{{ old('qs_experience_years', 0) }}">
                    </div>

                    <div class="hire-field is-wide">
                        <label class="hire-label" for="qs_eligibility">Eligibility <span class="hire-required">*</span></label>
                        <input type="text" id="qs_eligibility" name="qs_eligibility" class="hire-input" required maxlength="255"
                               value="{{ old('qs_eligibility') }}" placeholder="Career Service Professional / Second Level Eligibility">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="competency">Competency</label>
                        <input type="text" id="competency" name="competency" class="hire-input" maxlength="255"
                               value="{{ old('competency') }}" placeholder="N/A">
                    </div>

                    <div class="hire-field is-wide">
                        <label class="hire-label" for="required_skills">Required skills <span class="hire-required">*</span></label>
                        <textarea id="required_skills" name="required_skills" class="hire-textarea" required maxlength="2000"
                                  placeholder="Records management, report writing, MS Office">{{ old('required_skills') }}</textarea>
                        <p class="hire-hint">Separate each skill with a comma or a new line. These are what the skills subscore matches against.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Fit-score weights</h3>
                    <p class="table-sub">How the five criteria add up to 100</p>
                </div>
            </div>

            <div class="hire-section-body">
                <div class="hire-form-grid">
                    @php
                        // The scoring engine's starting split: education and skills
                        // carry the most weight under CSC QS, training the least.
                        $weightDefaults = [
                            'education' => 0.30,
                            'training' => 0.15,
                            'experience' => 0.20,
                            'eligibility' => 0.15,
                            'skills' => 0.20,
                        ];
                    @endphp

                    @foreach($weights as $criterion => $label)
                        <div class="hire-field">
                            <label class="hire-label" for="weight_{{ $criterion }}">{{ $label }} <span class="hire-required">*</span></label>
                            <input type="number" id="weight_{{ $criterion }}" name="weight_{{ $criterion }}" class="hire-input"
                                   required min="0" max="1" step="0.01"
                                   value="{{ old('weight_'.$criterion, $weightDefaults[$criterion]) }}">
                        </div>
                    @endforeach
                </div>

                <p class="hire-hint hire-form-note">
                    Entered as decimals — <strong>0.30</strong> is 30%. The five must total
                    <strong>1.00</strong>; the form rejects anything else.
                </p>
            </div>
        </section>

        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Dates and instructions</h3>
                    <p class="table-sub">When it runs, and what applicants are told</p>
                </div>
            </div>

            <div class="hire-section-body">
                <div class="hire-form-grid">
                    <div class="hire-field">
                        <label class="hire-label" for="posting_date">Posting date <span class="hire-required">*</span></label>
                        <input type="date" id="posting_date" name="posting_date" class="hire-input" required
                               value="{{ old('posting_date', now()->toDateString()) }}">
                    </div>

                    <div class="hire-field">
                        <label class="hire-label" for="closing_date">Closing date <span class="hire-required">*</span></label>
                        <input type="date" id="closing_date" name="closing_date" class="hire-input" required
                               value="{{ old('closing_date', now()->addDays(30)->toDateString()) }}">
                        <p class="hire-hint">The position is open all of this day, then lapses automatically.</p>
                    </div>

                    <div class="hire-field is-wide">
                        <label class="hire-label" for="instructions">Application instructions</label>
                        <textarea id="instructions" name="instructions" class="hire-textarea" maxlength="5000"
                                  placeholder="Where to file, what to attach, who to address…">{{ old('instructions') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="table-footer">
                <span class="hire-hint">The vacancy is stored in this system's own database.</span>
                <div class="hire-form-actions">
                    <a href="{{ route('admin.recruitment.vacancies') }}" class="btn-ghost">Cancel</a>
                    <button type="submit" class="btn-solid">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Create vacancy
                    </button>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection
