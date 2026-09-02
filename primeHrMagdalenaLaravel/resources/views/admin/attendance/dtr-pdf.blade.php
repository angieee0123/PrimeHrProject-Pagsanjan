<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Daily Time Record — {{ $employee['name'] }} — {{ $periodStart->format('M d, Y') }} to {{ $periodEnd->format('M d, Y') }}</title>
  <style>
  /* A4 portrait, and no page margin: every sheet is positioned at the
     template's own absolute page coordinates. */
  @page { size: 595.28pt 841.89pt; margin: 0; }

  html, body { margin: 0; padding: 0; }
  </style>
</head>
<body>
  @include('admin.attendance.partials.employee-attendance-logs-form')
</body>
</html>
