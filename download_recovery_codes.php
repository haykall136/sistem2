<?php
require('fpdf186/fpdf.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codes'])) {
    $codes = explode("\n", $_POST['codes']);

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Recovery Codes', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);

    foreach ($codes as $code) {
        $pdf->Cell(0, 10, $code, 0, 1);
    }

    $pdf->Output('D', 'Recovery_Codes.pdf');
    exit();
} else {
    echo "Invalid request.";
}
