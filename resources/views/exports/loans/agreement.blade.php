<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $loan->loan_product->name }} - {{ $loan->loan_number }}</title>

    <style>
        {!! file_get_contents(realpath(public_path('bootstrap-5.0.2/css/bootstrap.css'))) !!}
    </style>

    <style>
        * {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 12px;
        }

         th, td {
            border: 0.01rem solid black !important;
            border-collapse: collapse !important;
        }
            
       

        table { page-break-inside:auto; page-break-after:auto  }
        tr { page-break-inside:avoid; page-break-after:auto }

        .green-loan {
            background-color: #d9ead3 !important;
        }

        .font-times * {
            font-family: 'Times New Roman', Times, serif !important;
        }

        thead {display: table-header-group;}

    </style>
</head>
<body>
    <div class="">
        <table class="table table-bordered">
            <thead>
                <tr>

                    <th class="border-2" colspan="5">

                        <div class="d-flex justify-content-center">
                            <div class="p-2">
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(realpath(public_path('images/logo.png')))) }}"  style="width:130px;"
                                    class="img img-responsive" alt="Logo">
                            </div>
                            <div>
                                <div class="p-2 font-times">
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
                        
                    </th>

                </tr>

            </thead>
            <tbody>
                <tr >
                    <td class="border-left-2" >Name</td>
                    <td class="border-right-2" colspan="4">{{ $loan->member->full_name }}</td>
                </tr>
                <tr>
                    <td class="border-left-2">DSPACC ID NUMBER</td>
                    <td class=" border-right-2" colspan="4">{{ $loan->member->member_number }}</td>
                </tr>
                <tr>
                    <td class="border-left-2">HOME ADDRESS</td>
                    <td class=" border-right-2" colspan="4">{{ $loan->home_address }}</td>
                </tr>
                <tr>
                    <td class="border-left-2">TYPE OF LOAN</td>
                    <td class=" border-right-2" colspan="4">{{ $loan->loan_product->name }}</td>
                </tr>
                <tr>
                    <td class="border-left-2">RELEASED DATE</td>
                    <td class="green-loan">{{ $loan->released_date->format('M d, Y') }}</td>
                    <td class=" border-right-2" colspan="3"></td>
                </tr>
                <tr>
                    <td class="border-left-2">LOAN AMOUNT</td>
                    <td colspan="3"></td>
                    <td class="text-end green-loan border-right-2">{{ number_format($loan->principal_amount,2) }}</td>
                </tr>
                <tr>
                    <td  class="border-left-2 border-right-2" colspan="5">OTHER CHARGES</td>
                </tr>
                @foreach($loan->loan_fees as $fee)
                <tr>
                    <td class="border-left-2 text-center">*{{ $fee->loan_fee_template->name }}</td>
                    <td colspan="4" class="text-end border-right-2">{{ number_format($fee->amount, 2) }}</td>
                </tr>
                @endforeach

                <tr>
                    <td class="text-center border-left-2">TOTAL PROCEEDS</td>
                    <td colspan="4" class="text-end border-right-2">{{ number_format($loan->released_amount, 2) }}</td>
                </tr>
                <tr>
                    <th class="border-left-2">Principal</td>
                    <td>Interest Rate</td>
                    <td>Term in {{ ucwords($loan->loan_duration_type) }}</td>
                    <td  class="border-right-2" colspan="2">Payment Type</td>
                </tr>
                <tr>
                    <td class="border-left-2">{{ number_format($loan->principal_amount, 2) }}</td>
                    <td class="green-loan">{{ $loan->loan_interest }}%</td>
                    <td class="green-loan">{{ $loan->loan_duration }}</td>

                    <td class="border-right-2" colspan="2">{{ ucwords($loan->repayment_cycle) }}</td>
                </tr>


                <tr>
                    <td colspan="5" class="text-center fw-bold border-left-2 border-right-2">PAYMENT SCHEDULE</td>
                </tr>
                <tr>
                    <th class="border-left-2">Outstandng Principal Balance</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Amortization</th>
                    <th class="border-right-2">Due Date</th>
                </tr>
                @foreach($loan->loan_schedules()->orderBy('due_date', 'asc')->get() as $schedule)
                <tr>
                    <td class="border-left-2">{{ number_format(abs($schedule->principal_balance), 2) }}</td>
                    <td>{{ number_format($schedule->principal_amount, 2)  }}</td>
                    <td>{{ number_format($schedule->interest_amount, 2)  }}</td>
                    <td>{{ number_format($schedule->original_due_amount, 2) }}</td>
                    <td class="border-right-2">{{ $schedule->due_date->format('M d, Y') }}</td>
                </tr>
                @endforeach

                <tr>
                    <td class="green-loan border-left-2">Total</td>
                    <td class="green-loan">{{ number_format($loan->principal_amount, 2) }}</td>
                    <td class="green-loan">{{ number_format($loan->interest_amount, 2) }}</td>
                    <td class="green-loan">{{ number_format($loan->due_amount, 2) }}</td>
                    <td class="fw-bold border-right-2">Congratulations!!!</td>
                </tr>

                <tr class="border-bottom-0">
                    <td class="pb-3 pt-3 border-bottom-0 border-2" colspan="5">
                        <ul class="p-0 m-0" style="list-style-type: none;">
                            <li>CONDITIONAL CHARGES MAYBE IMPOSED
                                <ul>
                                    <li>a. Penalty of 2% per month against unpaid loan amortization.</li>
                                </ul>
                            </li>
                        </ul>

                    </td>
                </tr>
                <tr class="border-top-0">
                    <td class="border-end-0 border-top-0 border-left-2 border-2" colspan="2">
                        <div class="m-3">
                            <span class="fw-bold"> Released by:</span>
                            <div class="card m-start-4">
                                <div class="card-body text-center">
                                    <strong>MA. KAREN SOROÑO</strong>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="border-start-0 border-end-0 border-top-0  border-2"></td>
                    <td class="border-start-0 border-top-0 border-right-2 border-2" colspan="2">
                        <div class="m-3">
                            <span class="fw-bold"> Approved by:</span>
                            <div class="card m-start-4">

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