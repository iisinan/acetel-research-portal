<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Students Ongoing Milestones Report' }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #065f46;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .institution-name {
            font-size: 13px;
            font-weight: 800;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .institution-sub {
            font-size: 9.5px;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
        }
        .doc-subtitle {
            font-size: 10px;
            color: #047857;
            font-weight: 700;
        }
        
        /* Summary Grid */
        .summary-box {
            width: 100%;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 5px 10px;
            font-size: 9.5px;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-label {
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            font-size: 8.5px;
            width: 16%;
        }
        .summary-val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Section Header for each Milestone */
        .milestone-section {
            margin-top: 14px;
            margin-bottom: 14px;
            page-break-inside: auto;
        }
        .milestone-header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        .milestone-header-count {
            float: right;
            background-color: #065f46;
            color: #ffffff;
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 800;
        }

        /* Milestone Table */
        .milestone-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .milestone-table thead {
            display: table-header-group;
        }
        .milestone-table tr {
            page-break-inside: avoid;
        }
        .milestone-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #1e293b;
        }
        .milestone-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: top;
        }
        .milestone-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .student-name {
            font-weight: 700;
            color: #0f172a;
        }
        .matric-no {
            font-weight: 700;
            color: #047857;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9.5px;
        }
        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            background-color: #e2e8f0;
            color: #334155;
        }
        .status-in_progress {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-submitted {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-revision_required {
            background-color: #ffe4e6;
            color: #9f1239;
        }
        .status-partially_approved {
            background-color: #ccfbf1;
            color: #115e59;
        }
        .supervisor-name {
            color: #334155;
            font-size: 8.5px;
        }
        .thesis-title {
            color: #334155;
            font-style: italic;
            font-size: 8.5px;
        }

        /* Footer */
        .footer {
            margin-top: 16px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- Official Institutional Header --}}
    <div class="header">
        <div class="institution-name">Africa Centre of Excellence on Technology Enhanced Learning (ACETEL)</div>
        <div class="institution-sub">National Open University of Nigeria • Postgraduate Thesis Monitoring System</div>
        <div class="doc-title">{{ $title ?? 'Students Ongoing Milestones Report' }}</div>
        <div class="doc-subtitle">Candidate Master Register Categorized by Ongoing Milestone Stage</div>
    </div>

    {{-- Summary Grid --}}
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="summary-label">Total Ongoing:</td>
                <td class="summary-val">{{ $totalStudents }} Candidates</td>
                <td class="summary-label">Program Scope:</td>
                <td class="summary-val">{{ $programScope ?? 'All Programs' }}</td>
            </tr>
            <tr>
                <td class="summary-label">Active Stages:</td>
                <td class="summary-val">{{ count($groupedMilestones) }} Milestones</td>
                <td class="summary-label">Cohort Scope:</td>
                <td class="summary-val">{{ $cohortScope ?? 'All Cohorts' }}</td>
            </tr>
            <tr>
                <td class="summary-label">Scheduled Defences:</td>
                <td class="summary-val">{{ $scheduledCount }} Scheduled</td>
                <td class="summary-label">Generated On:</td>
                <td class="summary-val">{{ $generatedAt }} by {{ $generatedBy }}</td>
            </tr>
        </table>
    </div>

    {{-- Grouped Milestones --}}
    @forelse($groupedMilestones as $group)
        <div class="milestone-section">
            <div class="milestone-header">
                <span class="milestone-header-count">{{ count($group['milestones']) }} Candidates</span>
                <span>Milestone {{ $group['template']->order }}: {{ $group['template']->name }}</span>
            </div>

            <table class="milestone-table">
                <thead>
                    <tr>
                        <th style="width: 3%;" class="text-center">#</th>
                        <th style="width: 12%;">Matric Number</th>
                        <th style="width: 17%;">Student Name</th>
                        <th style="width: 13%;">Program & Level</th>
                        <th style="width: 10%;">Cohort</th>
                        <th style="width: 18%;">Thesis Title</th>
                        <th style="width: 13%;">Supervisor(s)</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 6%;">Defence Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['milestones'] as $idx => $m)
                        @php
                            $student = $m->thesis?->student;
                            $user = $student?->user;
                            $program = $student?->program?->code ?? ($student?->program?->name ?? 'N/A');
                            $level = $student?->level?->name ?? '';
                            $cohort = $student?->cohort?->name ?? 'N/A';
                            $title = $m->thesis?->title ?? 'Untitled Thesis';
                            $supervisors = implode(', ', $m->thesis?->getSupervisorNames() ?? []);
                            if (empty($supervisors)) $supervisors = 'None Assigned';
                            $status = $m->status;
                            $statusText = match($status) {
                                'in_progress' => 'In Progress',
                                'submitted' => 'Submitted',
                                'revision_required' => 'Revision Req.',
                                'partially_approved' => 'Partially Appr.',
                                default => ucfirst(str_replace('_', ' ', $status))
                            };
                            $defence = $m->defence_date ? \Carbon\Carbon::parse($m->defence_date)->format('M d, Y') : '—';
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="matric-no">{{ $student?->student_id_number ?? 'N/A' }}</td>
                            <td>
                                <div class="student-name">{{ $user?->name ?? 'Unknown Student' }}</div>
                                <div style="font-size: 8px; color: #64748b;">{{ $user?->email ?? '' }}</div>
                            </td>
                            <td>
                                <strong>{{ $program }}</strong>
                                @if($level)
                                    <span style="color: #64748b;">({{ $level }})</span>
                                @endif
                            </td>
                            <td>{{ $cohort }}</td>
                            <td class="thesis-title">{{ Str::limit($title, 85) }}</td>
                            <td class="supervisor-name">{{ $supervisors }}</td>
                            <td>
                                <span class="status-badge status-{{ $status }}">{{ $statusText }}</span>
                            </td>
                            <td>{{ $defence }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 30px; background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; margin-top: 20px;">
            <p style="font-size: 11px; font-weight: 700; color: #64748b;">No active students found matching the selected milestone criteria.</p>
        </div>
    @endforelse

    <div class="footer">
        Confidential Document • Generated for Institutional Academic Monitoring • ACETEL LMS • Printed: {{ $generatedAt }}
    </div>

</body>
</html>
