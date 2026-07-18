{{-- Performer Details Modal — populated entirely by showPerformerDetails() in the page script, no server data needed here. --}}
<div id="performerDetailsModal" style="display:none;position:fixed;inset:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(10px) saturate(140%);-webkit-backdrop-filter:blur(10px) saturate(140%);z-index:2000;align-items:center;justify-content:center;padding:20px">
    <div style="position:relative;background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.78));backdrop-filter:blur(28px) saturate(180%);-webkit-backdrop-filter:blur(28px) saturate(180%);border:1px solid rgba(255,255,255,.7);border-radius:24px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:inset 0 1px 0 rgba(255,255,255,.85), 0 30px 70px rgba(11,4,77,.28), 0 8px 24px rgba(15,23,42,.1)">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:28px 32px;border-bottom:1px solid rgba(15,23,42,.06)">
            <div style="display:flex;align-items:center;gap:20px">
                <div id="modalPerformerAvatar" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid rgba(255,255,255,.8);box-shadow:0 8px 20px rgba(11,4,77,.15)"></div>
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                        <h3 id="modalPerformerName" style="margin:0;font-size:22px;font-weight:700;color:#0b044d"></h3>
                        <span id="modalPerformerRank" style="font-size:22px"></span>
                    </div>
                    <p id="modalPerformerPosition" style="margin:0 0 2px;font-size:14px;color:#64748b;font-weight:500"></p>
                    <p id="modalPerformerDept" style="margin:0;font-size:13px;color:#94a3b8"></p>
                </div>
            </div>
            <button onclick="closePerformerModal()" style="background:rgba(15,23,42,.05);border:1px solid rgba(15,23,42,.06);font-size:18px;color:#64748b;cursor:pointer;width:36px;height:36px;border-radius:999px;display:flex;align-items:center;justify-content:center;transition:all .2s" onmouseover="this.style.background='rgba(11,4,77,.1)';this.style.color='#0b044d'" onmouseout="this.style.background='rgba(15,23,42,.05)';this.style.color='#64748b'">&times;</button>
        </div>
        <div style="padding:32px">
            <div style="background:linear-gradient(135deg, rgba(239,246,255,.85) 0%, rgba(219,234,254,.7) 100%);backdrop-filter:blur(16px) saturate(160%);-webkit-backdrop-filter:blur(16px) saturate(160%);border:1px solid rgba(255,255,255,.6);border-radius:20px;padding:24px;margin-bottom:20px;box-shadow:inset 0 1px 0 rgba(255,255,255,.7), 0 8px 24px rgba(59,130,246,.08)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                    <div>
                        <p style="font-size:11px;color:#1e40af;font-weight:700;letter-spacing:0.5px;margin:0 0 4px">ATTENDANCE PERFORMANCE</p>
                        <p id="modalPeriodLabel" style="font-size:12px;color:#64748b;margin:0"></p>
                    </div>
                    <div style="text-align:right">
                        <p id="modalAttendanceRate" style="font-size:42px;font-weight:800;color:#1e40af;margin:0;line-height:1"></p>
                        <p style="font-size:11px;color:#1e40af;margin:6px 0 0;font-weight:600">Attendance Rate</p>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                    <div style="background:rgba(255,255,255,.65);backdrop-filter:blur(12px) saturate(160%);-webkit-backdrop-filter:blur(12px) saturate(160%);border:1px solid rgba(255,255,255,.7);border-radius:14px;padding:16px;text-align:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.05)">
                        <p style="font-size:28px;font-weight:800;color:#15803d;margin:0" id="modalPresentDays"></p>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;font-weight:600">Days Present</p>
                    </div>
                    <div style="background:rgba(255,255,255,.65);backdrop-filter:blur(12px) saturate(160%);-webkit-backdrop-filter:blur(12px) saturate(160%);border:1px solid rgba(255,255,255,.7);border-radius:14px;padding:16px;text-align:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.05)">
                        <p style="font-size:28px;font-weight:800;color:#8e1e18;margin:0" id="modalAbsentDays"></p>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;font-weight:600">Days Absent</p>
                    </div>
                    <div style="background:rgba(255,255,255,.65);backdrop-filter:blur(12px) saturate(160%);-webkit-backdrop-filter:blur(12px) saturate(160%);border:1px solid rgba(255,255,255,.7);border-radius:14px;padding:16px;text-align:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.05)">
                        <p style="font-size:28px;font-weight:800;color:#c9a227;margin:0" id="modalLateDays"></p>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;font-weight:600">Late Arrivals</p>
                    </div>
                </div>
            </div>

            <div style="background:rgba(248,250,252,.65);backdrop-filter:blur(20px) saturate(160%);-webkit-backdrop-filter:blur(20px) saturate(160%);border:1px solid rgba(255,255,255,.6);border-radius:20px;padding:24px;margin-bottom:20px;box-shadow:inset 0 1px 0 rgba(255,255,255,.7), 0 4px 16px rgba(15,23,42,.05)">
                <p style="font-size:13px;color:#0b044d;font-weight:700;margin:0 0 18px">Performance Breakdown</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Total Working Days</span>
                            <span id="modalWorkingDays" style="font-size:15px;color:#0b044d;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Present Days</span>
                            <span id="modalPresentDays2" style="font-size:15px;color:#15803d;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Absent Days</span>
                            <span id="modalAbsentDays2" style="font-size:15px;color:#8e1e18;font-weight:700"></span>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Late Instances</span>
                            <span id="modalLateDays2" style="font-size:15px;color:#c9a227;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Performance Tier</span>
                            <span id="modalTier" style="font-size:11px;padding:5px 12px;border-radius:999px;font-weight:700"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:13px;color:#64748b;font-weight:500">Attendance Rate</span>
                            <span id="modalRate2" style="font-size:15px;color:#1e40af;font-weight:700"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:linear-gradient(135deg, rgba(255,251,235,.8) 0%, rgba(254,243,199,.6) 100%);backdrop-filter:blur(16px) saturate(160%);-webkit-backdrop-filter:blur(16px) saturate(160%);border:1px solid rgba(245,158,11,.18);border-radius:20px;padding:24px;box-shadow:inset 0 1px 0 rgba(255,255,255,.6), 0 4px 16px rgba(245,158,11,.06)">
                <p style="font-size:13px;color:#0b044d;font-weight:700;margin:0 0 14px">🏆 Why Top Performer?</p>
                <div id="modalReason" style="font-size:13px;color:#64748b;line-height:1.7"></div>
            </div>
        </div>
    </div>
</div>
