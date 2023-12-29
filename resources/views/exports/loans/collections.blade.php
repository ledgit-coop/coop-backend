<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
table.unstyledTable {
  font-size: 13px;
  font-family: 'Times New Roman', Times, serif;
  border: 1px solid #000000;
  width: 100%;
  border-collapse: collapse;
}
table.unstyledTable td, table.unstyledTable th {
  border: 1px solid #7F7F7F;
  padding: 5px 5px;
}
table.unstyledTable thead {
  background: #489513;
  border-bottom: 0px solid #444444;
}
table.unstyledTable th {
  font-weight:600;
}
table.unstyledTable thead th {
  color: #FFFFFF;
  border-left: 0px solid #D0E4F5;
}
table.unstyledTable thead th:first-child {
  border-left: none;
}

.higlight {
  background-color: #e8e8e8;
}
 
table { page-break-inside:auto; page-break-after:auto  }
        tr { page-break-inside:avoid; page-break-after:auto }

    </style>

 
</head>
<body>
    <table class="unstyledTable">
        <thead>
            <tr>
                <th colspan="7">Loan Collections</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
                @php
                  $schedules = $loan->loan_schedules->filter(function($schedule) {
                    return $schedule->almost_due || $schedule->overdue;
                  });
                @endphp
                @if($schedules->count())
                  <tr class="higlight">
                      <th>Loan#: {{ $loan->loan_number }}</th>
                      <th>Type: {{ $loan->loan_product->name }}</th>
                      <th>Name: {{ $loan->member->full_name }}</th>
                      <th colspan="4">Address: {{ $loan->member->full_present_address }}</th>
                  </tr>
                  <tr>
                    <th>Due Date</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Penalty</th>
                    <th>Due Amount</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                  </tr>
                  @foreach($schedules as $schedule)
                      <tr>
                          <td>{{ $schedule->due_date->format('M d, Y') }}</td>
                          <td>{{ number_format($schedule->principal_amount, 2) }}</td>
                          <td>{{ number_format($schedule->interest_amount, 2) }}</td>
                          <td>{{ number_format($schedule->penalty_amount, 2) }}</td>
                          <td>{{ number_format($schedule->due_amount, 2) }}</td>
                          <td>{{ number_format($schedule->amount_paid, 2) }}</td> 
                          <td>{{ $schedule->due_humans }}</td>
                      </tr>
                     
                  @endforeach
                  <tr>
                    <td colspan="3"></td>
                    <th>Total Due</th>
                    <td colspan="3">{{ number_format($schedules->sum('due_amount'), 2) }}</td>
                     
                  </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>