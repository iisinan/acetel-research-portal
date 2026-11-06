<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 22px 28px 25px 28px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #047857;
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
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin: 3px 0 2px 0;
        }
        .doc-subtitle {
            font-size: 10.5px;
            color: #047857;
            font-weight: 700;
        }
        
        /* Summary Box */
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
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-label {
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            width: 16%;
        }
        .summary-val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Group Header Card */
        .group-header-card {
            margin-top: 10px;
            margin-bottom: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .group-badge {
            display: inline-block;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 7px;
            border-radius: 3px;
            margin-bottom: 3px;
        }
        .group-title {
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
            margin: 1px 0 2px 0;
        }
        .group-meta {
            font-size: 9.5px;
            color: #475569;
        }
        
        /* Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        .schedule-table thead {
            display: table-header-group;
        }
        .schedule-table tr {
            page-break-inside: avoid;
        }
        .schedule-table th {
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 7px;
            text-align: left;
            border: 1px solid #065f46;
        }
        .schedule-table td {
            padding: 5px 7px;
            border: 1px solid #cbd5e1;
            font-size: 9.5px;
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
            font-size: 10px;
        }
        .date-badge {
            font-weight: 700;
            color: #0f172a;
        }
        .time-badge {
            color: #334155;
            font-weight: 700;
        }
        .link-text {
            color: #1d4ed8;
            font-size: 8.5px;
            font-weight: 700;
            text-decoration: underline;
        }

        /* Group Footer & Main Footer */
        .group-footer {
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 8.5px;
            color: #64748b;
            font-style: italic;
            text-align: right;
        }
        .main-footer {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
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
        <div class="doc-subtitle">{{ $template->name }} — Master Timetable & Panel Schedule</div>
    </div>

    {{-- Master Summary Grid --}}
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="summary-label">Milestone:</td>
                <td class="summary-val">{{ $template->name }}</td>
                <td class="summary-label">Presentation Period:</td>
                <td class="summary-val">{{ $startingDate }} &ndash; {{ $endingDate }}</td>
            </tr>
            <tr>
                <td class="summary-label">Cohort(s):</td>
                <td class="summary-val">{{ $cohortsDisplay }}</td>
                <td class="summary-label">Total Scheduled:</td>
                <td class="summary-val">{{ count($scheduledMilestones) }} Candidates</td>
            </tr>
            <tr>
                <td class="summary-label">Discipline Groups:</td>
                <td class="summary-val" colspan="3">{{ $groupBreakdown }}</td>
            </tr>
            <tr>
                <td class="summary-label">Generated On:</td>
                <td class="summary-val" colspan="3">{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    {{-- Sections Grouped by Discipline --}}
    @foreach($groups as $grpIndex => $grp)
        @if($grpIndex > 0)
            <div style="page-break-before: always;"></div>
        @endif

        {{-- Group Header Callout --}}
        <div class="group-header-card" style="border-left: 6px solid {{ $grp['border_color'] }}; background-color: {{ $grp['bg_color'] }};">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: middle; width: 52%;">
                        <div class="group-badge" style="background-color: {{ $grp['color'] }};">
                            {{ $grp['name'] }} GROUP
                        </div>
                        <div class="group-title" style="color: {{ $grp['color'] }};">
                            {{ $grp['name'] }} Presentation Schedule
                        </div>
                        <div class="group-meta">
                            <strong>{{ $grp['count'] }} Candidates Scheduled</strong> &bull; Period: <strong>{{ $grp['start_date'] }} &ndash; {{ $grp['end_date'] }}</strong>
                        </div>
                    </td>
                    <td style="vertical-align: middle; width: 48%; text-align: right;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="text-align: right; padding-bottom: 3px;">
                                    <span style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: #475569;">Virtual Presentation Room (Zoom):</span><br>
                                    @if(!empty($grp['zoom_link']))
                                        <a href="{{ $grp['zoom_link'] }}" target="_blank" style="font-size: 9.5px; font-weight: 700; color: #1d4ed8; text-decoration: underline;">
                                            {{ $grp['zoom_link'] }}
                                        </a>
                                    @else
                                        <span style="font-size: 9.5px; color: #64748b; font-style: italic;">Link distributed by department</span>
                                    @endif
                                </td>
                            </tr>
                            @if(!empty($grp['examiners']))
                            <tr>
                                <td style="text-align: right; padding-top: 2px;">
                                    <span style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: #475569;">Examiners / Panel:</span>
                                    <span style="font-size: 9px; font-weight: 700; color: #0f172a;">
                                        {{ implode(', ', $grp['examiners']) }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Candidates Table for This Group --}}
        <table class="schedule-table">
            <thead>
                <tr>
                    <th style="width: 4%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};" class="text-center">#</th>
                    <th style="width: 13%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Matric Number</th>
                    <th style="width: 19%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Candidate Name</th>
                    <th style="width: 22%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Program</th>
                    <th style="width: 10%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Cohort</th>
                    <th style="width: 13%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Presentation Date</th>
                    <th style="width: 9%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Slot Time</th>
                    <th style="width: 10%; background-color: {{ $grp['header_bg'] }}; border-color: {{ $grp['header_bg'] }};">Meeting Link</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grp['items'] as $cIndex => $sm)
                    @php
                        $student = $sm->thesis?->student;
                        $user = $student?->user;
                        $program = $student?->program;
                        $cohort = $student?->cohort;
                        $dateFormatted = $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('D, M d, Y') : 'N/A';
                        $timeFormatted = $sm->defence_time ? \Carbon\Carbon::parse($sm->defence_time)->format('h:i A') : 'TBD';
                        $roomLink = !empty($sm->meeting_link) ? $sm->meeting_link : $grp['zoom_link'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $cIndex + 1 }}</td>
                        <td class="matric-no">{{ $student->student_id_number ?? 'N/A' }}</td>
                        <td class="student-name">{{ $user->name ?? 'Candidate' }}</td>
                        <td>{{ $program->name ?? ($program->code ?? 'N/A') }}</td>
                        <td>{{ $cohort->name ?? ($cohort->code ?? 'N/A') }}</td>
                        <td class="date-badge">{{ $dateFormatted }}</td>
                        <td class="time-badge">{{ $timeFormatted }}</td>
                        <td>
                            @if(!empty($roomLink))
                                <a href="{{ $roomLink }}" target="_blank" class="link-text">Join Zoom</a>
                            @elseif(!empty($sm->defence_location))
                                {{ $sm->defence_location }}
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 16px; color: #64748b; font-weight: 600;">
                            No candidates currently scheduled under this group.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="group-footer">
            End of {{ $grp['name'] }} Timetable &bull; {{ $grp['count'] }} candidate(s) scheduled
        </div>
    @endforeach

    <div class="main-footer">
        Generated automatically by ACETEL Thesis Monitoring System &bull; National Open University of Nigeria &bull; {{ $generatedAt }}
    </div>

</body>
</html>
