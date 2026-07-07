@extends('layouts.app')

@section('content')

@include('admin.topbar.adminTopbar')
@include('admin.notification.adminNotification')

<div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Pass Slips</p>
            <div class="stat-icon-wrap" style="background: #f0effe;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingSlips->total() + $approvedSlips->total() + $disapprovedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap" style="background: #fefce8;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background: #e8f9ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $approvedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Disapproved</p>
            <div class="stat-icon-wrap" style="background: #fdf0ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $disapprovedSlips->total() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Rejected</p>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
    <button class="tab-btn active" onclick="switchPassSlipTab('pending')">Pending</button>
    <button class="tab-btn" onclick="switchPassSlipTab('approved')">Approved</button>
    <button class="tab-btn" onclick="switchPassSlipTab('disapproved')">Disapproved</button>
</div>

@php $passSlipColors = ['#0b044d','#8e1e18','#1a0f6e','#5a0f0b','#2d1a8e','#6b3fa0']; @endphp

{{-- Pending Pass Slips --}}
<section class="table-section" id="passslip-pending-tab" style="border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #f0effe 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Pending Pass Slips</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Awaiting approval · {{ $pendingSlips->total() }} records</p>
        </div>
        <div class="table-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <select class="filter-select" id="filterPendingPassSlipDept" onchange="filterPassSlipRows('pending')" style="font-size: 11px; padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb; font-weight: 600; height: 34px;">
                <option value="all">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <button class="btn-export" style="background: #fff; color: #344054; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 11px; padding: 0 14px; height: 34px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Time Out</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Time In</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingSlips as $slip)
                <tr class="passslip-pending-row" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">{{ $slip->time_out ? \Carbon\Carbon::parse($slip->time_out)->format('g:i A') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">{{ $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('g:i A') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <form method="POST" action="{{ route('admin.passslip.approve', $slip->id) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" onclick="return confirm('Approve this pass slip?')" style="padding: 6px 12px; border: 1px solid #bbf7d0; border-radius: 8px; background: #f0fdf4; color: #15803d; font-size: 11px; font-weight: 700; cursor: pointer;">Approve</button>
                            </form>
                            <button onclick="disapprovePassSlip({{ $slip->id }})" style="padding: 6px 12px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #b42318; font-size: 11px; font-weight: 700; cursor: pointer;">Disapprove</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No pending pass slips</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Pending pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $pendingSlips->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $pendingSlips->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $pendingSlips->total() }}</strong> records</p>
    </div>
</section>

{{-- Approved Pass Slips --}}
<section class="table-section" id="passslip-approved-tab" style="display: none; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Approved Pass Slips</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Successfully approved · {{ $approvedSlips->total() }} records</p>
        </div>
        <div class="table-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <select class="filter-select" id="filterApprovedPassSlipDept" onchange="filterPassSlipRows('approved')" style="font-size: 11px; padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb; font-weight: 600; height: 34px;">
                <option value="all">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <button class="btn-export" style="background: #fff; color: #344054; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 11px; padding: 0 14px; height: 34px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Approved By</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Approved Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedSlips as $slip)
                <tr class="passslip-approved-row" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #15803d; font-weight: 600;">{{ $slip->approver->name ?? 'Admin User' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ $slip->approved_at ? $slip->approved_at->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No approved pass slips</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Approved pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $approvedSlips->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $approvedSlips->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $approvedSlips->total() }}</strong> records</p>
    </div>
</section>

{{-- Disapproved Pass Slips --}}
<section class="table-section" id="passslip-disapproved-tab" style="display: none; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #fdf0ef 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Disapproved Pass Slips</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Rejected requests · {{ $disapprovedSlips->total() }} records</p>
        </div>
        <div class="table-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <select class="filter-select" id="filterDisapprovedPassSlipDept" onchange="filterPassSlipRows('disapproved')" style="font-size: 11px; padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb; font-weight: 600; height: 34px;">
                <option value="all">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <button class="btn-export" style="background: #fff; color: #344054; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 11px; padding: 0 14px; height: 34px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disapprovedSlips as $slip)
                <tr class="passslip-disapproved-row" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">{{ $slip->employee->first_name ?? '' }} {{ $slip->employee->last_name ?? '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($slip->reason ?? '', 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ $slip->date ? $slip->date->format('M d, Y') : '' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #8e1e18;">{{ $slip->remarks ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No disapproved pass slips</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Disapproved pass slips will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedSlips->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedSlips->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedSlips->total() }}</strong> records</p>
    </div>
</section>

@vite(['resources/css/adminLeaveAndBenefits.css'])

<script>
function switchPassSlipTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById('passslip-' + tabName + '-tab').style.display = 'block';
}

function filterPassSlipRows(tabName) {
    const dept = document.getElementById('filter' + tabName.charAt(0).toUpperCase() + tabName.slice(1) + 'PassSlipDept').value;
    document.querySelectorAll('.passslip-' + tabName + '-row').forEach(row => {
        row.style.display = dept === 'all' || row.dataset.department === dept ? '' : 'none';
    });
}

function disapprovePassSlip(id) {
    const reason = prompt('Reason for disapproval:');
    if (!reason) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/passslip/${id}/disapprove`;
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="reason" value="${reason}">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
