<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Helper\Html as HtmlHelper;

class ReportExport implements FromView, WithStyles, WithColumnWidths
{
    protected $state;
    protected $ticketTotal;
    protected $ticketByApplication;
    protected $ticketByService;
    protected $ticketDuplicate;
    protected $completionReport;

    public function __construct($state, $ticketTotal, $ticketByApplication, $ticketByService, $ticketDuplicate, $completionReport)
    {
        $this->state = $state;
        $this->ticketTotal = $ticketTotal;
        $this->ticketByApplication = $ticketByApplication;
        $this->ticketByService = $ticketByService;
        $this->ticketDuplicate = $ticketDuplicate;
        $this->completionReport = $completionReport;
    }

    public function view(): View
    {
        return view('export.report', [
            'state' => $this->state,
            'ticketTotal' => $this->ticketTotal,
            'ticketByApplication' => $this->ticketByApplication,
            'ticketByService' => $this->ticketByService,
            'ticketDuplicate' => $this->ticketDuplicate,
            'completionReport' => $this->completionReport,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Auto wrap text agar height cell mengikuti konten
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getStyle($col)->getAlignment()->setWrapText(true);
            $sheet->getStyle($col)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        }

        $lastRow = $sheet->getHighestRow();

        // Atur tinggi row terakhir secara manual
        $sheet->getRowDimension($lastRow)->setRowHeight(500);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 30,
            'D' => 30,
            'E' => 30,
        ];
    }
}
