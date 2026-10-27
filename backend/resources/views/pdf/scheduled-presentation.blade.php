<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #047857;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .institution-name {
            font-size: 13px;
            font-weight: 800;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .institution-sub {
            font-size: 10px;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 4px 0 2px 0;
        }
        .doc-subtitle {
            font-size: 11px;
            color: #047857;
            font-weight: 700;
        }
        
        /* Summary Box */
        .summary-box {
            width: 100%;
            margin-bottom: 16px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 12px;
            font-size: 10.5px;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-label {
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            font-size: 9.5px;
            width: 18%;
        }
        .summary-val {
            font-weight: 700;
            color: #0f172a;
        }
        
        /* Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .schedule-table thead {
            display: table-header-group;
        }
        .schedule-table tr {
            page-break-inside: avoid;
        }
        .schedule-table th {
            background-color: #065f46;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #065f46;
        }
        .schedule-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            vertical-align: middle;
        }
        .schedule-table tbody tr:nth-child(even) {
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
            font-size: 10.5px;
        }
        .date-badge {
            font-weight: 700;
            color: #0f172a;
        }
        .time-badge {
            color: #475569;
            font-weight: 600;
        }
        .link-text {
            color: #2563eb;
            font-size: 9px;
            word-break: break-all;
        }

        /* Footer */
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- Official Header --}}
    <div class="header">
        <div class="institution-name">Africa Centre of Excellence on Technology Enhanced Learning (ACETEL)</div>
        <div class="institution-sub">National Open University of Nigeria • Thesis Monitoring System</div>
        <div class="doc-title">{{ $title }}</div>
        <div class="doc-subtitle">{{ $template->name }} — Master Schedule</div>
    </div>

    {{-- Summary Information Grid --}}
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="summary-label">Milestone:</td>
                <td class="summary-val">{{ $template->name }}</td>
                <td class="summary-label">Starting Date:</td>
                <td class="summary-val">{{ $startingDate }}</td>
            </tr>
            <tr>
                <td class="summary-label">Cohort(s):</td>
                <td class="summary-val">{{ $cohortsDisplay }}</td>
                <td class="summary-label">Ending Date:</td>
                <td class="summary-val">{{ $endingDate }}</td>
            </tr>
            <tr>
                <td class="summary-label">Total Scheduled:</td>
                <td class="summary-val">{{ count($scheduledMilestones) }} Candidates</td>
                <td class="summary-label">Generated On:</td>
                <td class="summary-val">{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    {{-- Candidate Schedule Table --}}
    <table class="schedule-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">#</th>
                <th style="width: 14%;">Matric Number</th>
                <th style="width: 22%;">Student Name</th>
                <th style="width: 17%;">Program</th>
                <th style="width: 11%;">Cohort</th>
                <th style="width: 14%;">Presentation Date</th>
                <th style="width: 8%;">Time</th>
                <th style="width: 10%;">Meeting / Venue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scheduledMilestones as $index => $sm)
                @php
                    $student = $sm->thesis?->student;
                    $user = $student?->user;
                    $program = $student?->program;
                    $cohort = $student?->cohort;
                    $dateFormatted = $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('D, M d, Y') : 'N/A';
                    $timeFormatted = $sm->defence_time ? \Carbon\Carbon::parse($sm->defence_time)->format('h:i A') : 'TBD';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="matric-no">{{ $student->student_id_number ?? 'N/A' }}</td>
                    <td class="student-name">{{ $user->name ?? 'Candidate' }}</td>
                    <td>{{ $program->name ?? ($program->code ?? 'N/A') }}</td>
                    <td>{{ $cohort->name ?? ($cohort->code ?? 'N/A') }}</td>
                    <td class="date-badge">{{ $dateFormatted }}</td>
                    <td class="time-badge">{{ $timeFormatted }}</td>
                    <td>
                        @if(!empty($sm->meeting_link))
                            <a href="{{ $sm->meeting_link }}" target="_blank" class="link-text">Join Link</a>
                        @elseif(!empty($sm->defence_location))
                            {{ $sm->defence_location }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 24px; color: #64748b; font-weight: 600;">
                        No students are currently scheduled for this presentation milestone.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated automatically by ACETEL Thesis Monitoring System • Page 1 of 1
    </div>

</body>
</html>
