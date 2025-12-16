<?php

namespace App\Support;

class NabEvaluator
{
    /**
     * Evaluasi apakah nilai memenuhi NAB berdasarkan parameter
     * 
     * @param string $parameter Nama parameter (pencahayaan, debu_total, kudr_suhu, dll)
     * @param float $value Nilai hasil pengukuran
     * @return array ['category' => 'diatas_nab'|'dibawah_nab', 'meets_nab' => bool, 'unit' => string]
     */
    public static function evaluate(string $parameter, float $value): array
    {
        $result = [
            'category' => 'dibawah_nab',
            'meets_nab' => true,
            'unit' => '',
        ];

        switch ($parameter) {
            case 'pencahayaan':
                // NAB < 300 → Tidak memenuhi (merah)
                // NAB ≥ 300 → Memenuhi (hijau)
                $result['unit'] = 'Lux Meter';
                if ($value < 300) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'debu_total':
                // NAB > 10 → Tidak memenuhi (merah)
                // NAB ≤ 10 → Memenuhi (hijau)
                $result['unit'] = 'mg/m³';
                if ($value > 10) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_suhu':
                // NAB 23 sampai 26 → Memenuhi (hijau)
                // NAB lebih atau kurang dari 23-26 → Tidak memenuhi (merah)
                $result['unit'] = '°C';
                if ($value < 23 || $value > 26) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_rh':
                // NAB 40 sampai 60 → Memenuhi (hijau)
                // NAB lebih atau kurang dari 40-60 → Tidak memenuhi (merah)
                $result['unit'] = '%';
                if ($value < 40 || $value > 60) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_pergerakan_udara':
                // NAB > 0,03 → Tidak memenuhi (merah)
                // NAB ≤ 0,03 → Memenuhi (hijau)
                $result['unit'] = 'm/dt';
                if ($value > 0.03) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_hcoh':
                // NAB > 100 → Tidak memenuhi (merah)
                // NAB ≤ 100 → Memenuhi (hijau)
                $result['unit'] = 'µg/m³';
                if ($value > 100) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_co2':
                // NAB > 1000 → Tidak memenuhi (merah)
                // NAB ≤ 1000 → Memenuhi (hijau)
                $result['unit'] = 'Bds';
                if ($value > 1000) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_co':
                // NAB > 8,7 → Tidak memenuhi (merah)
                // NAB ≤ 8,7 → Memenuhi (hijau)
                $result['unit'] = 'Bds';
                if ($value > 8.7) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_no2':
                // NAB > 150 → Tidak memenuhi (merah)
                // NAB ≤ 150 → Memenuhi (hijau)
                $result['unit'] = 'µg/m³';
                if ($value > 150) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_oksidan':
                // NAB > 120 → Tidak memenuhi (merah)
                // NAB ≤ 120 → Memenuhi (hijau)
                $result['unit'] = 'µg/m³';
                if ($value > 120) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            case 'kudr_oksigen':
                // NAB 19,5 sampai 23,5 → Memenuhi (hijau)
                // NAB lebih atau kurang dari 19,5-23,5 → Tidak memenuhi (merah)
                $result['unit'] = '%';
                if ($value < 19.5 || $value > 23.5) {
                    $result['category'] = 'diatas_nab';
                    $result['meets_nab'] = false;
                }
                break;

            default:
                // Default: jika parameter tidak dikenal, anggap memenuhi NAB
                $result['category'] = 'dibawah_nab';
                $result['meets_nab'] = true;
                break;
        }

        return $result;
    }
}

