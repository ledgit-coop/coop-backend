<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $loan->loan_product->name }} - {{ $loan->loan_number }}</title>
    <link href="{{ asset('bootstrap-5.0.2/css/bootstrap.min.css') }}" rel="stylesheet" />
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        table {
            border-color: black !important;
        }
    </style>
</head>
<body>
    <div class="p-5">
        <table class="table table-bordered">
            <thead>
                <tr>

                    <td colspan="5">

                        <div class="d-flex justify-content-center">
                            <div class="p-2">
                                <img src="{{ asset('images/logo.png') }}" style="width:130px;"
                                    class="img img-responsive" alt="Logo">
                            </div>
                            <div>
                                <div class="p-2">
                                    <h1>Dalan Sa Pag Asenso</h1>
                                    <h2>Credit Cooperative</h2>
                                </div>

                                <span class="d-flex flex-column p-2 fw-bold">
                                    <span>Basak-Marigondon RD. 3H Lapu-Lapu City 6015</span>
                                    <span>Landmark: Back of Kokies Petshop Across Basak Brgy Hall</span>
                                    <span>Contact Number : 0950-681-0191</span>
                                    <span>Email :admin@dalansaapagasenso.org</span>
                                </span>
                            </div>
                        </div>
                        
                    </td>

                </tr>

            </thead>
            <tbody>
                <tr>
                    <td>Name</td>
                    <td colspan="4">{{ $loan->member->full_name }}</td>
                </tr>
                <tr>
                    <td>DSPACC ID NUMBER</td>
                    <td colspan="4">{{ $loan->member->member_number }}</td>
                </tr>
                <tr>
                    <td>HOME ADDRESS</td>
                    <td colspan="4">{{ $loan->home_address }}</td>
                </tr>
                <tr>
                    <td>TYPE OF LOAN</td>
                    <td colspan="4">{{ $loan->loan_product->name }}</td>
                </tr>
                <tr>
                    <td>RELEASED DATE</td>
                    <td colspan="4">{{ $loan->released_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td>LOAN AMOUNT</td>
                    <td colspan="4" class="text-end">{{ number_format($loan->principal_amount,2) }}</td>
                </tr>
                <tr>
                    <td colspan="5">OTHER CHARGES</td>
                </tr>
                @foreach($loan->loan_fees as $fee)
                <tr>
                    <td class="text-center">*{{ $fee->loan_fee_template->name }}</td>
                    <td colspan="4" class="text-end">{{ number_format($fee->amount, 2) }}</td>
                </tr>
                @endforeach

                <tr>
                    <td class="text-center">TOTAL PROCEEDS</td>
                    <td colspan="4" class="text-end">{{ number_format($loan->released_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Principal</td>
                    <td>Interest Rate</td>
                    <td>Term in {{ ucwords($loan->loan_duration_type) }}</td>
                    <td colspan="2">Payment Type</td>
                </tr>
                <tr>
                    <td>{{ $loan->principal_amount }}</td>
                    <td>{{ $loan->loan_interest }}%</td>
                    <td>{{ $loan->loan_duration }}</td>

                    <td colspan="2">{{ ucwords($loan->repayment_cycle) }}</td>
                </tr>


                <tr>
                    <td colspan="5" class="text-center fw-bold">PAYMENT SCHEDULE</td>
                </tr>
                <tr>
                    <th>Outstandng Principal Balance</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Amortization</th>
                    <th>Due Date</th>
                </tr>
                @foreach($loan->loan_schedules as $schedule)
                <tr>
                    <td>{{ abs($schedule->principal_balance ) }}</td>
                    <td>{{ $schedule->principal_amount }}</td>
                    <td>{{ $schedule->interest_amount }}</td>
                    <td>{{ $schedule->due_amount }}</td>

                    <td>{{ $schedule->due_date->format('M d, Y') }}</td>

                </tr>
                @endforeach

                <tr>
                    <td>Total</td>
                    <td>{{ $loan->principal_amount }}</td>
                    <td>{{ $loan->interest_amount }}</td>
                    <td>{{ $loan->due_amount }}</td>
                    <td>Congratulations!!!</td>
                </tr>

                <tr>
                    <td class="pb-3 pt-3" colspan="5">
                        <ul class="p-0 m-0" style="list-style-type: none;">
                            <li>CONDITIONAL CHARGES MAYBE IMPOSED
                                <ul>
                                    <li>a. Penalty of 2% per month against unpaid loan amortization.</li>
                                </ul>
                            </li>
                        </ul>

                    </td>
                </tr>
                <tr>
                    <td class="border-end-0" colspan="2">
                        <div class="m-3">
                            <span> Approved by:</span>
                            <div class="card m-start-5">
                                <div class="card-body text-center">
                                    <strong>MA. KAREN SOROÑO</strong>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="border-start-0 border-end-0"></td>
                    <td class="border-start-0" colspan="2">
                        <div class="m-3">
                            <span> Approved by:</span>
                            <div class="card m-start-5">

                                <div class="card-body text-center">
                                    <strong>CHAD BERNIE LABRADO</strong>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>