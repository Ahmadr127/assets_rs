<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Collection $logs;

    public function __construct(Collection $logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'Row Index',
            'Status',
            'Kode',
            'Kode Manual',
            'PO',
            'Asset Number',
            'Nama Fixed Asset',
            'Tipe',
            'Taksiran Umur',
            'Nilai Awal',
            'Efektif Mulai',
            'Lokasi',
            'Status Asset',
            'Kondisi',
            'Vendor',
            'Brand',
            'PIC',
            'Errors',
            'Duplicate Key',
        ];
    }

    public function map($log): array
    {
        $mappedData = $log->mapped_data ?? [];
        $errors = $log->errors ?? [];

        return [
            $log->row_index,
            strtoupper($log->status),
            $mappedData['kode'] ?? '',
            $mappedData['kode_manual'] ?? '',
            $mappedData['po'] ?? '',
            $mappedData['asset_number'] ?? '',
            $mappedData['nama_fixed_asset'] ?? '',
            $mappedData['tipe_fixed_asset'] ?? '',
            $mappedData['taksiran_umur'] ?? '',
            $mappedData['nilai_awal'] ?? '',
            $mappedData['efektif_mulai'] ?? '',
            $mappedData['lokasi'] ?? '',
            $mappedData['status'] ?? '',
            $mappedData['kondisi'] ?? '',
            $mappedData['vendor'] ?? '',
            $mappedData['brand'] ?? '',
            $mappedData['pic'] ?? '',
            $this->formatErrors($errors),
            $log->duplicate_key ?? '',
        ];
    }

    protected function formatErrors($errors): string
    {
        if (empty($errors)) {
            return '';
        }

        if (is_array($errors)) {
            $formatted = [];
            foreach ($errors as $field => $messages) {
                if (is_array($messages)) {
                    $formatted[] = "$field: " . implode(', ', $messages);
                } else {
                    $formatted[] = "$field: $messages";
                }
            }
            return implode('; ', $formatted);
        }

        return (string) $errors;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }
}
