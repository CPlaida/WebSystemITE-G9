<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Accountant extends BaseController
{
    public function __construct()
    {
        // Ensure user is logged in and has accountant role
        if (!in_array(session()->get('role'), ['accounting', 'admin'])) {
            return redirect()->to('/auth/login');
        }
    }

    public function reports($type = 'income')
    {
        $data = [
            'title' => ucfirst($type) . ' Report',
            'activeMenu' => 'reports',
            'subMenu' => $type
        ];

        // Load the appropriate view based on the report type
        return view('Roles/Accountant/Reports/' . ucfirst($type), $data);
    }

    public function exportIncomePdf()
    {
        // Get filter parameters
        $fromDate = $this->request->getGet('from');
        $toDate = $this->request->getGet('to');
        
        // TODO: Fetch data from database based on filters
        $data = [
            'title' => 'Income Report',
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            // Add your report data here
        ];
        
        // Return PDF (you'll need to implement the PDF generation)
        $dompdf = new \Dompdf\Dompdf();
        $html = view('exports/income_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("income-report-{$fromDate}-to-{$toDate}.pdf", ["Attachment" => true]);
    }

    public function exportExpensesPdf()
    {
        // Get filter parameters
        $fromDate = $this->request->getGet('from');
        $toDate = $this->request->getGet('to');
        $category = $this->request->getGet('category');
        
        // TODO: Fetch data from database based on filters
        $data = [
            'title' => 'Expenses Report',
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'category' => $category,
            // Add your report data here
        ];
        
        // Return PDF (you'll need to implement the PDF generation)
        $dompdf = new \Dompdf\Dompdf();
        $html = view('exports/expenses_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("expenses-report-{$fromDate}-to-{$toDate}.pdf", ["Attachment" => true]);
    }

    public function exportExpensesExcel()
    {
        // Get filter parameters
        $fromDate = $this->request->getGet('from');
        $toDate = $this->request->getGet('to');
        $category = $this->request->getGet('category');
        
        // TODO: Fetch data from database based on filters
        $data = [
            'title' => 'Expenses Report',
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'category' => $category,
            // Add your report data here
        ];
        
        // Return Excel (you'll need to implement the Excel generation)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Add your Excel generation code here
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="expenses-report.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
