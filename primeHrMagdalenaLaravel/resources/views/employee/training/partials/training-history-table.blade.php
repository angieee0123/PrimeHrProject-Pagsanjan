        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Training History</p>
                    <p class="table-sub">Section IV — Learning &amp; Development (CSC PDS format)</p>
                </div>
                <div class="table-actions">
                    <a href="{{ route('employee.training.export') }}" class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Export to PDS
                    </a>
                    {{-- .btn-export supplies the pill shape its neighbour uses;
                         .tr-btn-primary-solid only takes over the colours. It
                         was .modal-btn-primary — a modal-footer class on a
                         toolbar button, which is why it rendered square. --}}
                    <button type="button" class="btn-export tr-btn-primary-solid" onclick="openAddTrainingModal()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add New Training
                    </button>
                </div>
            </div>

            <div class="training-filter-bar">
                <div class="training-filter-chips" role="group" aria-label="Filter by status">
                    <button type="button" class="training-filter-chip active" data-status-filter="all" onclick="setStatusFilter('all', this)">All</button>
                    <button type="button" class="training-filter-chip" data-status-filter="verified" onclick="setStatusFilter('verified', this)">Verified</button>
                    <button type="button" class="training-filter-chip" data-status-filter="pending" onclick="setStatusFilter('pending', this)">Pending</button>
                    <button type="button" class="training-filter-chip" data-status-filter="rejected" onclick="setStatusFilter('rejected', this)">Rejected</button>
                </div>
                <select id="trainingPositionFilter" class="filter-select training-position-filter" onchange="filterPermanentTraining()" aria-label="Filter by position type">
                    <option value="">All position types</option>
                    <option value="Managerial">Managerial</option>
                    <option value="Supervisory">Supervisory</option>
                    <option value="Technical">Technical</option>
                    <option value="Clerical">Clerical</option>
                </select>
            </div>

                <div class="table-wrapper">
                <table class="payroll-table training-pds-table" id="trainingHistoryTable">
                        <thead>
                            <tr>
                            <th>Title of Seminar / Conference / Training Program</th>
                            <th>Inclusive Dates</th>
                            <th>No. of Hours</th>
                            <th>Type of Position</th>
                            <th>Conducted / Sponsored By</th>
                            <th>Verification Status</th>
                                <th class="row-menu-head">Actions</th>
                            </tr>
                        </thead>
                    <tbody id="trainingHistoryBody">
                        @forelse($trainings as $t)
                        @php
                            $cat = $t->ldCategory();
                            $badgeClass = match($t->position_type) {
                                'Managerial'  => 'managerial',
                                'Supervisory' => 'supervisory',
                                'Technical'   => 'technical',
                                'Clerical'    => 'clerical',
                                default       => 'technical',
                            };
                        @endphp
                        <tr class="training-row-{{ $t->status }} row-{{ $t->status }}"
                            data-status="{{ $t->status }}"
                            data-hours="{{ $t->status === 'verified' ? $t->hours : 0 }}"
                            data-category="{{ $cat }}"
                            data-position="{{ $t->position_type }}"
                            data-ref="{{ $t->ref_doc_no }}"
                            @if($t->rejected_reason) data-reject-note="{{ $t->rejected_reason }}" @endif>
                            <td>
                                <div class="training-title-wrap">
                                    <span class="training-title-text">{{ $t->title }}</span>
                                    <span class="training-ref-doc">{{ $t->ref_doc_no }}</span>
                                    </div>
                                </td>
                            <td class="training-table-date">
                                <span class="training-date-from">{{ $t->date_from ? $t->date_from->format('M d, Y') : '—' }}</span>
                                <span class="training-date-sep">–</span>
                                <span class="training-date-to">{{ $t->date_to ? $t->date_to->format('M d, Y') : '—' }}</span>
                            </td>
                            <td>
                                <span class="training-hours-pill {{ $t->status !== 'verified' ? 'training-hours-pill-pending' : '' }}">
                                    {{ $t->hours }}
                                </span>
                                </td>
                            <td><span class="type-badge {{ $badgeClass }}">{{ $t->position_type }}</span></td>
                            <td>{{ $t->conducted_by }}</td>
                            <td>
                                @if($t->status === 'verified')
                                    <span class="verify-badge verified">Verified</span>
                                @elseif($t->status === 'rejected')
                                    <span class="verify-badge rejected" title="{{ $t->rejected_reason }}">Rejected</span>
                                @else
                                    <span class="verify-badge pending">Pending</span>
                                @endif
                                </td>
                            <td class="row-menu-cell">
                                @if($t->certificate_path || $t->status === 'pending')
                                <button type="button" class="row-menu-btn" data-menu="trainingRowMenu{{ $t->id }}"
                                        onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                        title="Actions" aria-label="Actions for {{ $t->title }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                                <div class="row-menu" id="trainingRowMenu{{ $t->id }}" role="menu" aria-label="Training record actions">
                                    @if($t->certificate_path)
                                    <a href="{{ route('employee.training.certificate', $t->id) }}" target="_blank" role="menuitem" class="row-menu-item" onclick="closeRowMenu()">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View certificate
                                    </a>
                                    @endif
                                    @if($t->status === 'pending')
                                    @if($t->certificate_path)<div class="row-menu-sep"></div>@endif
                                    <form method="POST" action="{{ route('employee.training.delete', $t->id) }}" class="row-menu-form"
                                        data-confirm-title="Delete this training record?"
                                        data-confirm="{{ Str::limit($t->title, 60) }} will be removed from your training history. This cannot be undone."
                                        data-confirm-action="Delete record"
                                        data-confirm-cancel="Keep it">
                                        @csrf @method('DELETE')
                                        <button type="submit" role="menuitem" class="row-menu-item is-danger">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Delete record
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @else
                                    <span class="row-menu-empty">—</span>
                                @endif
                                </td>
                            </tr>
                        @empty
                        <tr id="noTrainingRow">
                            <td colspan="7" style="text-align:center;padding:40px;color:var(--gp-text-soft);">
                                No training records yet. Click “Add New Training” to submit your first record.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            <div class="table-footer">
                <span>Showing <strong id="trainingRowCount">{{ $trainings->count() }}</strong> training record(s)</span>
            </div>
        </div>
