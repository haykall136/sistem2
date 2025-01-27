<?php
require('fpdf186/fpdf.php');
include 'dbconnect.php';

// Validate request_id
if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {
    die("Request ID is not specified.");
}

$request_id = $_GET['request_id'];

// Fetch holiday request details
$request_sql = "SELECT hr.*, u.nama_sebenar, u.position, u.remaining_leave_days 
                FROM holiday_requests hr
                JOIN users u ON hr.user_id = u.id
                WHERE hr.id = '$request_id'";
$request_result = mysqli_query($condb, $request_sql);
$request_data = mysqli_fetch_assoc($request_result);

if (!$request_data) {
    die("Invalid Request ID.");
}

// Calculate remaining leave days after deduction
$days_applied = (strtotime($request_data['end_date']) - strtotime($request_data['start_date'])) / (60 * 60 * 24) + 1;
$remaining_before = $request_data['remaining_leave_days'] + $days_applied;
$remaining_after = $request_data['remaining_leave_days'];

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

// Add Logo
$pdf->Image('logoYAUborang.png', 90, 10, 30); // Adjust 'x', 'y', and size as needed
$pdf->SetY(40); // Move below the logo

// Add Name Below Logo
$pdf->SetFont('Arial', '', 15 ); // Bold and larger font
$pdf->Cell(190, 10, 'YAYASAN AMMIRUL UMMAH', 0, 1, 'C'); // Centered text

// Company Registration Number at the Top Right
$pdf->SetXY(170, 10); // Adjust position
$pdf->Cell(30, 10, '545667-X', 0, 0, 'R'); // Align text to the right
$pdf->SetY(55);

// Title
$pdf->SetFont('Arial', 'B', 13); // Set font to Arial Bold Size 16
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(190, 10, 'SURAT PERMOHONAN CUTI', 0, 1, 'C');
$pdf->Ln(8);

// Divider
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(8);

// Application Details
$pdf->SetFont('Arial', '', 12); // Regular font
$pdf->Write(8, "Saya memohon kebenaran untuk Cuti Rehat Bergaji, cuti kecemasan selama "); // Regular text

$pdf->SetFont('Arial', 'B', 12); // Bold font
$pdf->Write(8, "$days_applied hari"); // Bold text for days applied

$pdf->SetFont('Arial', '', 12); // Back to regular font
$pdf->Write(8, " bermula daripada hari/tarikh "); // Continue regular text

$pdf->SetFont('Arial', 'B', 12); // Bold font
$pdf->Write(8, date('d/m/Y', strtotime($request_data['start_date']))); // Bold start date

$pdf->SetFont('Arial', '', 12); // Back to regular font
$pdf->Write(8, " sehingga "); // Continue regular text

$pdf->SetFont('Arial', 'B', 12); // Bold font
$pdf->Write(8, date('d/m/Y', strtotime($request_data['end_date']))); // Bold end date

$pdf->Ln(10); // Add a new line
$pdf->Ln(10);

// User Information
$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(60, 8, "Nama Pemohon", 0, 0, 'L'); // Label without the colon

$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, ": " . $request_data['nama_sebenar'], 0, 1, 'L'); // Value with colon

$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(60, 8, "Jabatan", 0, 0, 'L'); // Label without the colon

$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, ": " . $request_data['position'], 0, 1, 'L'); // Value with colon

$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(60, 8, "Baki Cuti Sebelum Ditolak", 0, 0, 'L'); // Label without the colon

$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, ": $remaining_before hari", 0, 1, 'L'); // Value with colon

$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(60, 8, "Baki Cuti Selepas Ditolak", 0, 0, 'L'); // Label without the colon

$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, ": $remaining_after hari", 0, 1, 'L'); // Value with colon

$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(60, 8, "Tujuan Cuti", 0, 0, 'L'); // Label without the colon

$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, ": " . $request_data['reason'], 0, 1, 'L'); // Value with colon
$pdf->Ln(8);

// Divider
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(8);

// Approval Details
$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(0, 8, "Zubaidah Binti Ismail (Ketua Jabatan):", 0, 1);
$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, $request_data['result'], 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(0, 8, "Ahmad Ibrahim Ridhwan Bin Ismail (CEO):", 0, 1);
$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, $request_data['result'], 0, 1);
$pdf->Ln(8);

// Divider
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(8);

// Summary Statement
$pdf->SetFont('Arial', 'B', 12); // Bold font for the name
$pdf->Cell(0, 8, $request_data['nama_sebenar'] . ',', 0, 1); // Append comma after the name

$pdf->SetFont('Arial', '', 11); // Regular font for summary
$pdf->Write(8, "Permohonan cuti tuan/puan ");

$pdf->SetFont('Arial', 'B', 11); // Bold font for result
$pdf->Write(8, $request_data['result'] . " "); // Bold result

$pdf->SetFont('Arial', '', 11); // Back to regular font
$pdf->Write(8, "selama ");

$pdf->SetFont('Arial', 'B', 11); // Bold font for days applied
$pdf->Write(8, "$days_applied hari "); // Bold days applied

$pdf->SetFont('Arial', '', 11); // Back to regular font
$pdf->Write(8, "bermula daripada hari/tarikh ");

$pdf->SetFont('Arial', 'B', 11); // Bold font for start date
$pdf->Write(8, date('d/m/Y', strtotime($request_data['start_date'])) . " "); // Bold start date

$pdf->SetFont('Arial', '', 11); // Back to regular font
$pdf->Write(8, "sehingga ");

$pdf->SetFont('Arial', 'B', 11); // Bold font for end date
$pdf->Write(8, date('d/m/Y', strtotime($request_data['end_date'])) . "."); // Bold end date
$pdf->Ln(11);

// Final Leave Balance
$pdf->SetFont('Arial', 'B', 12); // Bold font for labels
$pdf->Cell(60, 8, "Baki Cuti Rehat", 0, 0, 'L'); // Label without the colon

$pdf->SetFont('Arial', '', 11); // Regular font for values
$pdf->Cell(0, 8, ": $remaining_after hari", 0, 1, 'L'); // Value with colon
$pdf->Ln(6);

// Divider
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(8);

// Footer
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 0, "Cetakan berkomputer ini tidak memerlukan tandatangan.", 0, 1, 'C');

// Output PDF
$pdf->Output('I', 'leave_application.pdf');
?>
