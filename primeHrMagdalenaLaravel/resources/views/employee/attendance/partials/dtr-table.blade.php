                {{-- ── TABLE ── --}}
                <div class="ddtr-table-card">
                    <div id="detailedDTRLoading" class="ddtr-loading">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#9aa1b5" stroke-width="2.5" class="ddtr-spin">
                            <circle cx="12" cy="12" r="10" opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" opacity=".75"/>
                        </svg>
                        <p>Loading attendance records…</p>
                    </div>

                    <div class="ddtr-table-scroll">
                        <table class="detailed-dtr-table ddtr-hidden" id="detailedDTRTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>AM <span class="ddtr-th-hint">8:00 – 12:00</span></th>
                                    <th>PM <span class="ddtr-th-hint">1:00 – 5:00</span></th>
                                    <th>OT</th>
                                    <th>Undertime</th>
                                    <th>Late</th>
                                    <th>Total Hrs</th>
                                    <th>Accredited Hrs</th>
                                    <th>Leave Deduction</th>
                                </tr>
                            </thead>
                            <tbody id="detailedDTRBody"></tbody>
                        </table>
                    </div>
                </div>
