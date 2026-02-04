@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                @php
                    $primaryBadge = $employee->badges && $employee->badges->isNotEmpty()
                        ? $employee->badges->first()
                        : null;
                    $badgeColor = $primaryBadge?->color ?: '#1d4ed8';
                @endphp
                <h1>
                    {{ $employee->name }}
                    @if($primaryBadge)
                        <span
                            class="tag-pill"
                            title="{{ $primaryBadge->name }} @if($primaryBadge->description) – {{ $primaryBadge->description }} @endif"
                            style="margin-left:0.5rem; font-size:0.8rem; background:#f9fafb; color:#111827; border-radius:999px; padding:0.1rem 0.6rem; display:inline-flex; align-items:center; gap:0.35rem; border:1px solid {{ $badgeColor }};"
                        >
                            <span style="width:0.7rem;height:0.7rem;border-radius:999px;background:{{ $badgeColor }};display:inline-block;"></span>
                            {{ $primaryBadge->name }}
                        </span>
                    @endif
                </h1>
                <p>{{ $employee->job_position ?? 'Employee' }}</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.edit', $employee) }}" class="button-secondary">Edit profile</a>
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
            </div>
        </header>

        <div class="profile-grid">
            <div class="profile-main">
                <div class="form-section">
                    <div class="section-header">
                        <h3>Contact</h3>
                    </div>
                    <div class="form-grid">
                        <div>
                            <p class="metric-label">Work email</p>
                            <p>{{ $employee->work_email ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Work phone</p>
                            <p>{{ $employee->work_phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Work mobile</p>
                            <p>{{ $employee->work_mobile ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <h3>Role &amp; organisation</h3>
                    </div>
                    <div class="form-grid">
                        <div>
                            <p class="metric-label">Department</p>
                            <p>{{ $employee->department ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Work zone</p>
                            <p>{{ $employee->work_zone ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <h3>Tags</h3>
                    </div>
                    <p>
                        @if(!empty($employee->tags) && is_array($employee->tags))
                            @foreach($employee->tags as $tag)
                                <span class="tag-pill">{{ $tag }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No tags assigned.</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="profile-side">
                @if($employee->photo_path)
                    <div class="profile-photo">
                        <img src="{{ asset('storage/'.$employee->photo_path) }}" alt="{{ $employee->name }}" style="max-width: 96px; border-radius: 999px;">
                    </div>
                @endif
                @if($currentContract)
                    <div class="profile-card">
                        <h3>Current contract</h3>
                        <p><strong>Reference:</strong> {{ $currentContract->reference }}</p>
                        <p><strong>Period:</strong>
                            {{ optional($currentContract->start_date)->format('Y-m-d') }} –
                            {{ optional($currentContract->end_date)->format('Y-m-d') ?? 'Open-ended' }}
                        </p>
                        <p><strong>Salary:</strong> {{ number_format($currentContract->salary_amount ?? 0, 2) }}</p>
                        <p><strong>TA:</strong> {{ number_format($currentContract->travel_allowance ?? 0, 2) }}</p>
                        <p><strong>DA:</strong> {{ number_format($currentContract->dearness_allowance ?? 0, 2) }}</p>
                        <p><strong>Bonus:</strong> {{ number_format($currentContract->bonus ?? 0, 2) }}</p>
                        <a href="{{ route('admin.employees.contracts.index', $employee) }}" class="button-secondary">View contracts</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h2>Quick links</h2>
                <p>Jump into detailed views for this employee.</p>
            </div>
        </header>
        <div class="button-group">
            <a href="{{ route('admin.employees.contracts.index', $employee) }}" class="button-secondary">Payroll / contracts</a>
            <a href="{{ route('admin.employees.allowances.index', $employee) }}" class="button-secondary">Allowances (TA/DA)</a>
            <a href="{{ route('admin.employees.equipment.index', $employee) }}" class="button-secondary">Equipment</a>
            <a href="{{ route('admin.employees.leaves.index', $employee) }}" class="button-secondary">Leave</a>
            <a href="{{ route('admin.employees.locations.index', $employee) }}" class="button-secondary">Locations</a>
            <a href="{{ route('admin.employees.badges.grant-form', $employee) }}" class="button-secondary">Grant badge</a>
        </div>
    </section>

    @if($recentAllowances->isNotEmpty())
        <section class="panel">
            <header>
                <h2>Recent allowances</h2>
                <p>Latest TA/DA/bonus slips recorded for this employee.</p>
            </header>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAllowances as $allowance)
                        <tr>
                            <td>{{ $allowance->date?->format('Y-m-d') }}</td>
                            <td>{{ $allowance->type }}</td>
                            <td>{{ $allowance->reference ?? '—' }}</td>
                            <td>{{ number_format($allowance->amount, 2) }}</td>
                            <td>{{ ucfirst($allowance->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

</div>
@endsection
