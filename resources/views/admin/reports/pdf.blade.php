<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Report PDF</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 14px; color: #222; }
        h1, h2, h3 { color: #1f497d; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 18px; }
        .info-table { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .info-table td { padding: 4px 8px; }
        .participants { font-style: italic; color: #444; }
        .content { margin-top: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>AUDIT REPORT</h1>
        <h2>{{ $audit->name }}</h2>
        <table class="info-table">
            <tr><td><strong>Country:</strong></td><td>{{ $countryName }}</td></tr>
            <tr><td><strong>Review Code:</strong></td><td>{{ $audit->review_code }}</td></tr>
            <tr><td><strong>Report Type:</strong></td><td>{{ $reportType }}</td></tr>
            <tr><td><strong>Generated:</strong></td><td>{{ $generatedAt }}</td></tr>
        </table>
        <div class="participants">
            <strong>Participants:</strong> {{ $participants }}
        </div>
    </div>
    <div class="content">
        {!! nl2br(e($reportContent)) !!}
    </div>
</body>
</html>
