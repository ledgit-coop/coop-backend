<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Net Surplus Report</title>

    <style>
        {!! file_get_contents(realpath(public_path('bootstrap-5.0.2/css/bootstrap.css'))) !!}
    </style>
	<style>
		*{
			color: black !important;
		}
 
		table { page-break-inside:auto; page-break-after:auto; width: 100%; }
		tr { page-break-inside:avoid; page-break-after:auto }
 
		body {  font-family: Arial, Helvetica, sans-serif !important; font-size:10pt  !important;}

		.center {
			margin-left: auto;
			margin-right: auto;
		}

		.text-center {
			margin-left: auto;
			margin-right: auto;
		}

		.green-loan {
			background-color: #d9ead3 !important;
		}
	</style>
</head>
<body>
<div class="text-center">
	<h3>Allocation and Distribution of Net Surplus</h3>
	<p>Fiscal Date: {{ $netSurplus->from_date->format('Y-m-d') }} to {{ $netSurplus->to_date->format('Y-m-d') }}<br />
	Created By: {{ $netSurplus->createdBy->name }}</p>
</div>

<table class="table">
	<tbody>
		<tr>
			<td colspan="2"><strong>Figures on Operation</strong></td>
			 
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Interest Income on Loan</td>
			<td>{{ number_format($netSurplus->interest_income_on_loan, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Service Fees</td>
			<td>{{ number_format($netSurplus->service_fees, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Membership Fees</td>
			<td>{{ number_format($netSurplus->membership_fees, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Gross Surplus</td>
			<td>{{ number_format($netSurplus->gross_surplus, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Less: Operating Expenses</td>
			<td>({{ number_format($netSurplus->operating_expenses, 2) }})</td>
		</tr>
		<tr>
			<td><strong>&nbsp; &nbsp; Net Surplus for Allocation and Distribution</strong></td>
			<td>{{ number_format($netSurplus->net_suprplus_allocation_distribution, 2) }}</td>
		</tr>
		<tr>
			<td colspan="2"><strong>Allocation of Net Surplus</strong></td>
 		</tr>
		<tr>
			<td>&nbsp; &nbsp; Reserve Fund ({{ $netSurplus->reserve_fund_percent }}%)</td>
			<td>{{ number_format($netSurplus->reserve_fund, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Education and Training Fund ({{ $netSurplus->educational_training_fund_percent }}%)</td>
			<td>{{ number_format($netSurplus->educational_training_fund, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Due to CETF (Apex) 1/2 of ETF</td>
			<td>{{ number_format($netSurplus->educational_training_fund_due_cetf, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Local ETF (Apex) 1/2 of ETF</td>
			<td>{{ number_format($netSurplus->educational_training_fund_due_etf, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Optional Fund ({{ $netSurplus->optional_fund_percent }}%)</td>
			<td>{{ number_format($netSurplus->optional_fund, 2) }}</td>
		</tr>
		<tr>
			<td colspan="2"><strong>Allocation of Remainder for Interest and Patronage</strong></td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Interest on Share Capital ({{ $netSurplus->interest_on_share_capital_allocation_percent }}%)</td>
			<td>{{ number_format($netSurplus->interest_on_share_capital, 2) }}</td>
		</tr>
		<tr>
			<td>&nbsp; &nbsp; Patronage Refund ({{ $netSurplus->patronage_refund_allocation_percent }}%)</td>
			<td>{{ number_format($netSurplus->patronage_refund, 2) }}</td>
		</tr>
		<tr>
			<td><strong>Net Surplus Allocated and Distributed</strong></td>
			<td>{{ number_format($netSurplus->net_surplus_allocated_distributed, 2) }}</td>
		</tr>
		<tr>
			<td><strong>Rate of Interest on Share Capital (%)</strong></td>
			<td>{{ number_format($netSurplus->interest_on_share_capital_rate_interest, 2) }}</td>
		</tr>
		<tr>
			<td><strong>Rate of Patronage&nbsp;(%)</strong></td>
			<td>{{ number_format($netSurplus->patronage_refund_rate_interest, 2) }}</td>
		</tr>
	</tbody>
</table>


<h5>Net Surplus Distribution</h5>
<table class="table table-bordered">
	<thead>
		<tr>
			<td class="text-wrap">Members</td>
			@foreach($dates as $date)
				<td class="text-center">
					<div>{{ $date["month"] }}</div>
					<small>{{ $date["year"] }}</small>
				</td>
			@endforeach
			<td>Avg. Shares Months</td>
			<td class="green-loan">Int. Shares Capital</td>
			<td>Loan Interest</td>
			<td class="green-loan">Pat. Refund</td>
		</tr>
	</thead>
	<tbody>
		@foreach($memberShareCapitals as $key => $member)
			<tr>
				<td class="text-wrap">{{ $member->first()->first_name . " " . $member->first()->middle_name ?? "" . " " . $member->first()->surname }}</td>
				@foreach($dates as $date)
					<td  class="text-wrap">{{ number_format($member->where('month', $date["digit"])->where('year', $date["year"])->sum('total'), 2) }}</td>
				@endforeach
				<td class="text-wrap">{{ number_format($member->sum('total') / 12, 2) }}</td>
				<td class="green-loan">{{ number_format(($member->sum('total') / 12) * $netSurplus->interest_on_share_capital_rate_interest, 2) }}</td>
				<td class="text-wrap">{{ number_format($memberLoanInterest->where('id', $key)->sum('total'), 2) }}</td>
				<td class="green-loan">{{ number_format($memberLoanInterest->where('id', $key)->sum('total') * $netSurplus->patronage_refund_rate_interest, 2) }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

</body>
</html>