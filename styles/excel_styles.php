<?php
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

return [
    'colors' => [
        'header_bg' => 'FF4F81BD', // Professional blue
        'header_text' => 'FFFFFFFF', // White
        'row_even_bg' => 'FFDCE6F1', // Light blue for even rows
        'row_odd_bg' => 'FFFFFFFF', // White for odd rows
        'border' => 'FFB8CCE4', // Light border color
    ],
    'styles' => [
        'header' => [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFB8CCE4'],
                ],
            ],
        ],
        'border' => [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFB8CCE4'],
                ],
            ],
        ],
        'alignment_center' => [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ],
        'wrap_text' => [
            'alignment' => [
                'wrapText' => true,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ],
        'font' => [
            'font' => ['size' => 11, 'name' => 'Calibri'],
        ],
    ],
    'column_widths' => [
        'A' => 5,
        'B' => 20,
        'C' => 20,
        'D' => 15,
        'E' => 15,
        'F' => 20,
        'G' => 40,
    ],
];
?>