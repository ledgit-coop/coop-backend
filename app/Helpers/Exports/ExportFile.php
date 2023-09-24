<?php

namespace App\Helpers\Exports;

use App\Models\Loan;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Dompdf\Dompdf;

class ExportFile {
    public static function export(string $view, string $path, string $filename) {
 
        $Storage = Storage::disk('public');        
        $Storage->makeDirectory($path);
        $path = $path. $filename;
        $storagePath = $Storage->path($path);
     
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($view);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();

        // // Transform to pdf
        // Browsershot::html($view)
        //         ->newHeadless()
        //         ->noSandbox()
        //         ->emulateMedia('print')
        //         ->margins(10, 10, 10, 10)
        //         ->setNodeBinary(config('browsershot.node_path'))
        //         ->setNpmBinary(config('browsershot.npm_path'))
        //         ->setChromePath(config('browsershot.chrome_path'))
        //         ->savePdf("test.pdf");   
        
        // return $Storage->url($path);
    }

    public static function exportAgreement(Loan $loan) {
 
        $view = view('exports.loans.agreement', compact('loan'));

        $filename = $loan->member->full_name . "-" . $loan->loan_number . ".pdf";
        $path =  "loans/$loan->id/agreements/";
        
        return self::export($view, $path, $filename);
    }
}