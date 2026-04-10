<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Menampilkan halaman laporan transaksi
    public function transactions(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->paginate(15)->withQueryString();

        $totalAmount = $query->get()->sum('amount');
        $totalTransactions = $query->count();

        return view('admin.reports.transactions', compact('transactions', 'totalAmount', 'totalTransactions'));
    }

    // Export ke Excel
    public function exportExcel(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->get();

        $totalTransactions = $transactions->count();
        $totalAmount = $transactions->sum('amount');
        $average = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Aplikasi
        $sheet->setCellValue('A1', 'BAGJA BARBERSHOP');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'JL Arief Rohman Haikin No. 45, Subang, Jawa Barat');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setSize(10);

        $sheet->setCellValue('A3', 'LAPORAN TRANSAKSI');
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';
        $sheet->setCellValue('A4', 'Periode: ' . $startDate . ' - ' . $endDate);
        $sheet->mergeCells('A4:H4');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(10);

        // Ringkasan
        $rowSummary = 6;
        $sheet->setCellValue('A' . $rowSummary, 'Total Transaksi:');
        $sheet->setCellValue('B' . $rowSummary, $totalTransactions);
        $sheet->setCellValue('D' . $rowSummary, 'Total Pendapatan:');
        $sheet->setCellValue('E' . $rowSummary, 'Rp ' . number_format($totalAmount, 0, ',', '.'));
        $sheet->setCellValue('G' . $rowSummary, 'Rata-rata:');
        $sheet->setCellValue('H' . $rowSummary, 'Rp ' . number_format($average, 0, ',', '.'));
        $sheet->getStyle('A' . $rowSummary . ':H' . $rowSummary)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowSummary . ':H' . $rowSummary)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF5F5F5');

        // Header Tabel
        $rowHeader = 8;
        $headers = ['ID', 'Kode Booking', 'Customer', 'Barber', 'Layanan', 'Total', 'Metode', 'Tanggal'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $rowHeader, $header);
            $col++;
        }
        $headerRange = 'A' . $rowHeader . ':H' . $rowHeader;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD4AF37');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Data Tabel
        $rowData = 9;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $rowData, $transaction->id);
            $sheet->setCellValue('B' . $rowData, $transaction->booking->booking_code ?? '-');
            $sheet->setCellValue('C' . $rowData, $transaction->booking->customer->name ?? '-');
            $sheet->setCellValue('D' . $rowData, $transaction->booking->barber->name ?? '-');
            $sheet->setCellValue('E' . $rowData, $transaction->booking->services->pluck('name')->implode(', '));
            $sheet->setCellValue('F' . $rowData, 'Rp ' . number_format($transaction->amount, 0, ',', '.'));
            $sheet->setCellValue('G' . $rowData, ucfirst($transaction->payment_method));
            $sheet->setCellValue('H' . $rowData, $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-');
            $rowData++;
        }

        if ($transactions->count() > 0) {
            $dataRange = 'A' . $rowHeader . ':H' . ($rowData - 1);
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $footerRow = $rowData + 2;
        $sheet->setCellValue('A' . $footerRow, 'Dicetak oleh: ' . (auth()->user()->name ?? 'Admin'));
        $sheet->setCellValue('H' . $footerRow, 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->getStyle('A' . $footerRow . ':H' . $footerRow)->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A' . $footerRow . ':H' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $spreadsheet->getActiveSheet()->setShowGridlines(false);

        $filename = 'laporan_transaksi_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Export ke PDF
    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->get();

        $totalAmount = $transactions->sum('amount');
        $totalTransactions = $transactions->count();

        $pdf = Pdf::loadView('admin.reports.transactions_pdf', compact('transactions', 'totalAmount', 'totalTransactions', 'request'));
        return $pdf->download('laporan_transaksi_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }

    // Query filter yang digunakan bersama
    private function getFilteredQuery(Request $request)
    {
        $query = Transaction::with(['booking.customer', 'booking.barber', 'booking.services'])
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('booking.customer', function ($cq) use ($search) {
                    $cq->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('booking', function ($bq) use ($search) {
                    $bq->where('booking_code', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return $query;
    }
}