<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

include '../../config/koneksi.php';

// Query
$query = "
    SELECT 
        bookings.id, 
        users.username AS username, 
        rooms.name AS room_name, 
        bookings.date, 
        bookings.start_time, 
        bookings.end_time,
        bookings.bidang,
        bookings.agenda_rapat
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN rooms ON bookings.room_id = rooms.id
    ORDER BY bookings.date ASC, bookings.start_time ASC
";

$result = mysqli_query($koneksi, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($koneksi));
}

// Buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ======================
// Fungsi Tanggal Indonesia
function tanggalIndonesia($tanggal)
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $parts = explode('-', $tanggal); // format: d-m-Y
    $hari = ltrim($parts[0], '0');
    $bulanNama = $bulan[(int)$parts[1]];
    $tahun = $parts[2];
    return "{$hari} {$bulanNama} {$tahun}";
}

// ======================
// Kop Surat
$sheet->mergeCells('A1:G1')->setCellValue('A1', 'PEMERINTAH KABUPATEN TEGAL');
$sheet->mergeCells('A2:G2')->setCellValue('A2', 'DINAS LINGKUNGAN HIDUP');
$sheet->mergeCells('A3:G3')->setCellValue('A3', 'Jalan Professor Muhammad Yamin, Kudaile, Kec. Slawi, Kab.Tegal.');
$sheet->mergeCells('A4:G4')->setCellValue('A4', 'Website: dlh.tegalkab.go.id, Email:  dlhkabtegal@gmail.com

');

// Gaya judul
$sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Tambah garis bawah
$sheet->getStyle('A5:G5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

// ======================
// Logo
$logo = new Drawing();
$logo->setName('Logo DLH');
$logo->setDescription('Logo DLH');
$logo->setPath(__DIR__ . '/../../image/dlh.png');
$logo->setHeight(70);
$logo->setCoordinates('A1');
$logo->setOffsetX(10);
$logo->setWorksheet($sheet);

// ======================
// Header Tabel
$headers = ['No', 'Nama Pemesan', 'Ruang Rapat', 'Tanggal', 'Waktu', 'Bidang', 'Agenda Rapat'];
$startRow = 6;
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $startRow, $header);
    $col++;
}

// Gaya header tabel
$headerRange = "A{$startRow}:G{$startRow}";
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB0C4DE');

// Lebar kolom
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(30);

// ======================
// Isi Data
$rowNum = $startRow + 1;
$index = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $date = (new DateTime($row['date']))->format('d-m-Y');
    $time = (new DateTime($row['start_time']))->format('H:i') . ' - ' . (new DateTime($row['end_time']))->format('H:i');

    $sheet->setCellValue('A' . $rowNum, $index++);
    $sheet->setCellValue('B' . $rowNum, $row['username']);
    $sheet->setCellValue('C' . $rowNum, $row['room_name']);
    $sheet->setCellValue('D' . $rowNum, $date);
    $sheet->setCellValue('E' . $rowNum, $time);
    $sheet->setCellValue('F' . $rowNum, $row['bidang']);
    $sheet->setCellValue('G' . $rowNum, $row['agenda_rapat']);
    $rowNum++;
}

// Border dan alignment isi tabel
$dataRange = "A{$startRow}:G" . ($rowNum - 1);
$sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle("A" . ($startRow + 1) . ":A" . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("D" . ($startRow + 1) . ":E" . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Auto filter
$sheet->setAutoFilter("A{$startRow}:G{$startRow}");

// ======================
// Tanda Tangan
date_default_timezone_set("Asia/Jakarta");
$tanggal = date('d-m-Y');
$tanggalFormatted = tanggalIndonesia($tanggal);
$ttdRow = $rowNum + 2;

$sheet->setCellValue("F{$ttdRow}", "Tegal, {$tanggalFormatted}");
$sheet->setCellValue("F" . ($ttdRow + 1), "Kepala Dinas");
$sheet->setCellValue("F" . ($ttdRow + 4), "EDY SUCIPTO, S.K.M,. M.Si");
$sheet->setCellValue("F" . ($ttdRow + 5), "NIP.197109071998031007");
$sheet->getStyle("F{$ttdRow}:G" . ($ttdRow + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// ======================
// Export
$year = date('Y');
$filename = "RiwayatPesanan_{$year}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>