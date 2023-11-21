<?php

namespace App\Helpers\Exports;

use App\Models\AnnualReturn;
use App\Models\Loan;

class ExportFile {

    public static function exportAgreement(Loan $loan) {
        $view = view('exports.loans.agreement', compact('loan'))->render();        
        return $view;
    }

    public static function exportLoanTerms(Loan $loan) {
        $view = view('exports.loans.terms', compact('loan'))->render();        
        return $view;
    }

    public static function exportNetSurplus(AnnualReturn $netSurplus, $memberShareCapitals, $memberLoanInterest, $dates) {
        $view = view('exports.net-surplus.report', compact('netSurplus', 'memberShareCapitals', 'dates', 'memberLoanInterest'))->render();        
        return $view;
    }
}