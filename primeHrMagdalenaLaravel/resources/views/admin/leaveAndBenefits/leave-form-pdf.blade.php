<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>CS Form No. 6 — {{ $application->application_number }}</title>
  <style>
  @page { margin: 0.4in; }
  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9pt;
    color: #000;
    margin: 0;
    padding: 0;
  }
  </style>
</head>
<body>
  @include('admin.leaveAndBenefits.partials.cs-form-no-6')
</body>
</html>
