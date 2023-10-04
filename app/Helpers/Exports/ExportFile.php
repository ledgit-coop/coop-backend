<?php

namespace App\Helpers\Exports;

use App\Models\Loan;
use Exception;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
use Spatie\Browsershot\Browsershot;

class ExportFile {
    public static function export(string $view, string $path, string $filename) {
 
        $Storage = Storage::disk('public');        
        $Storage->makeDirectory($path);
        $path = $path. $filename;
        $storagePath = $Storage->path($path);
        
        $pdf = new Pdf(array(
            'binary' => '/home/whtmcawt/etc/dspacc.com/wktohtml/local/bin/wkhtmltopdf',
        ));
        $pdf->addPage($view);

        if (!$pdf->saveAs($storagePath))
            throw new Exception($pdf->getError(), 1);

        return $Storage->url($path);
    }

    public static function exportAgreement(Loan $loan) {
 
        $view = view('exports.loans.agreement', compact('loan'))->render();

        $filename = $loan->member->full_name . "-" . $loan->loan_number . ".pdf";
        $path =  "loans/$loan->id/agreements/";
        
        return self::export($view, $path, $filename);
    }
}