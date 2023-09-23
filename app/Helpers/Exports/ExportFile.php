<?php

namespace App\Helpers\Exports;

use App\Models\Loan;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class ExportFile {
    public static function export(string $view, string $path, string $filename) {
 
        $Storage = Storage::disk('public');        
        $Storage->makeDirectory($path);
        $path = $Storage->path($path. $filename);

        dd($path);
        // Transform to pdf
        $pdf = Browsershot::html($view)
                ->newHeadless()
                ->noSandbox()
                ->emulateMedia('print')
                ->margins(10, 10, 10, 10)
                ->setNodeBinary(config('browsershot.node_path'))
                ->setNpmBinary(config('browsershot.npm_path'))
                ->setChromePath(config('browsershot.chrome_path'))
                ->savePdf($path);

        
        
        
        return $Storage->url($path);
    }

    public static function exportAgreement(Loan $loan) {
 
        $view = view('exports.loans.agreement', compact('loan'));

        return $view;
        $filename = $loan->member->full_name . "-" . $loan->loan_number . ".pdf";
        $path =  "loans/$loan->id/agreements/";
        
        return self::export($view, $path, $filename);
    }
}