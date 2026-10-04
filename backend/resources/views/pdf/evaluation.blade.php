<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Evaluation Report - {{ $evaluation->defenceEvent->thesis->student->user->name ?? 'Unknown' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-placeholder {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin: 0;
        }
        h3 {
            color: #1e3a8a;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            color: #4b5563;
            font-size: 12px;
            text-transform: uppercase;
            width: 40%;
        }
        td {
            font-weight: bold;
        }
        .score-grid {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9fafb;
            margin-bottom: 30px;
        }
        .score-item {
            display: inline-block;
            width: 48%;
            margin-bottom: 15px;
        }
        .score-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .score-value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
        .total-score {
            text-align: center;
            padding: 20px;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            margin-top: 20px;
        }
        .total-number {
            font-size: 40px;
            font-weight: bold;
            color: #1e40af;
        }
        .verdict {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
        }
        .verdict-pass { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .verdict-minor { background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .verdict-major { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .verdict-fail { background-color: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }
        .comments {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px;
            white-space: pre-wrap;
            font-style: italic;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
            font-size: 12px;
            color: #64748b;
        }
        .signatures {
            margin-top: 60px;
        }
        .signature-line {
            width: 250px;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-placeholder">ACETEL INTEL</div>
        <h1 class="title">Official Defense Evaluation Record</h1>
    </div>

    <h3>Thesis Details</h3>
    <table>
        <tr>
            <th>Student Name</th>
            <td>{{ $evaluation->defenceEvent->thesis->student->user->name ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <th>Academic Program</th>
            <td>{{ $evaluation->defenceEvent->thesis->student->program->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Thesis Title</th>
            <td>{{ $evaluation->defenceEvent->thesis->title }}</td>
        </tr>
        <tr>
            <th>Event Type</th>
            <td>{{ strtoupper($evaluation->defenceEvent->type) }}</td>
        </tr>
        <tr>
            <th>Event Date</th>
            <td>{{ $evaluation->defenceEvent->event_date->format('F d, Y') }} at {{ $evaluation->defenceEvent->location }}</td>
        </tr>
    </table>

    <h3>Scoring</h3>
    <div class="score-grid">
        @php
            $scores = $evaluation->score;
            $total = $scores['total'] ?? (array_sum($scores) > 0 ? array_sum($scores) / 2 : 0);
        @endphp
        <div class="score-item">
            <div class="score-label">Originality & Contribution</div>
            <div class="score-value">{{ $scores['originality'] }} / 25</div>
        </div>
        <div class="score-item">
            <div class="score-label">Methodology & Rigor</div>
            <div class="score-value">{{ $scores['methodology'] }} / 25</div>
        </div>
        <div class="score-item">
            <div class="score-label">Presentation Quality</div>
            <div class="score-value">{{ $scores['presentation'] }} / 25</div>
        </div>
        <div class="score-item">
            <div class="score-label">Q&A Defense</div>
            <div class="score-value">{{ $scores['qa'] }} / 25</div>
        </div>
    </div>

    <div class="total-score">
        <div class="score-label">Total Score</div>
        <div class="total-number">{{ $total }} <span style="font-size: 20px; color: #60a5fa;">/ 40</span></div>
    </div>

    <div class="verdict 
        @if($evaluation->recommendation === 'pass') verdict-pass 
        @elseif($evaluation->recommendation === 'minor_revisions') verdict-minor
        @elseif($evaluation->recommendation === 'major_revisions') verdict-major
        @else verdict-fail @endif">
        Recommendation: {{ str_replace('_', ' ', $evaluation->recommendation) }}
    </div>

    @if($evaluation->comments)
        <h3>Examiner Comments</h3>
        <div class="comments">{{ $evaluation->comments }}</div>
    @endif

    <div class="signatures">
        <div style="float: left;">
            <div class="signature-line"></div>
            <div><strong>{{ $evaluation->evaluator->name ?? 'Unknown' }}</strong></div>
            <div style="font-size: 12px; color: #64748b;">Examiner</div>
            <div style="font-size: 12px; color: #64748b;">Date: {{ $evaluation->submitted_at?->format('F d, Y') }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Automatically generated by the Thesis Monitoring System.<br>
        Document ID: EV-{{ $evaluation->id }}<br>
        Generated on: {{ now()->format('F d, Y H:i:s') }}
    </div>
</body>
</html>
