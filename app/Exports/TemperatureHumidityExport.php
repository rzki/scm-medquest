<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemperatureHumidityExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    protected Collection $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function styles(Worksheet $sheet)
    {
        // Center align entire sheet content
        $highestRow = $sheet->getHighestRow(); // get total number of rows

        // Apply center alignment to all cells from A1 to last column & row
        $sheet->getStyle("A1:{$sheet->getHighestColumn()}{$highestRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Bold and center only for header row
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
    public function headings(): array
    {
        return [
            'Location / Serial Number',
            'Date',
            'Period',
            'Time (0800)',
            'Temperature (°C) (0800)',
            'RH (%) (0800)',
            'P.I.C (0800)',
            'Time (1100)',
            'Temperature (°C) (1100)',
            'RH (%) (1100)',
            'P.I.C (1100)',
            'Time (1400)',
            'Temperature (°C) (1400)',
            'RH (%) (1400)',
            'P.I.C (1400)',
            'Time (1700)',
            'Temperature (°C) (1700)',
            'RH (%) (1700)',
            'P.I.C (1700)',
            'Reviewed By',
            'Acknowledged By',
        ];
    }

    public function map($record): array
    {
        return [
            $record->location->location_name . ' / ' . $record->location->serial_number,
            Carbon::parse($record->date)->format('d'),
            strtoupper(Carbon::parse($record->period)->format('M Y')),
            $record->time_0800 ? Carbon::parse($record->time_0800)->format('H:i') : '-',
            $record->temp_0800 !== null ? $record->temp_0800 . ' °C' : '-',
            $record->rh_0800 !== null ? $record->rh_0800 . '%' : '-',
            $record->pic_0800 ?? '-',
            $record->time_1100 ? Carbon::parse($record->time_1100)->format('H:i') : '-',
            $record->temp_1100 !== null ? $record->temp_1100 . ' °C' : '-',
            $record->rh_1100 !== null ? $record->rh_1100 . '%' : '-',
            $record->pic_1100 ?? '-',
            $record->time_1400 ? Carbon::parse($record->time_1400)->format('H:i') : '-',
            $record->temp_1400 !== null ? $record->temp_1400 . ' °C' : '-',
            $record->rh_1400 !== null ? $record->rh_1400 . '%' : '-',
            $record->pic_1400 ?? '-',
            $record->time_1700 ? Carbon::parse($record->time_1700)->format('H:i') : '-',
            $record->temp_1700 !== null ? $record->temp_1700 . ' °C' : '-',
            $record->rh_1700 !== null ? $record->rh_1700 . '%' : '-',
            $record->pic_1700 ?? '-',
            $record->reviewed_by ?? '-',
            $record->acknowledged_by ?? '-',
        ];
    }
}
