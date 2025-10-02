<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KpiReportExport implements FromView
{
    protected $assessments;

    public function __construct($assessments)
    {
        $this->assessments = $assessments;
    }

    public function view(): View
    {
        return view('exports.kpi_report', [
            'assessments' => $this->assessments
        ]);
    }
}
