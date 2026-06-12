<?php

namespace App\Helpers;

class NumberFormatter
{
    public static function short($number, $precision = 2)
    {
        // Bersihkan input jika berupa string
        if (is_string($number)) {
            $number = str_replace(',', '', $number);
        }

        // Pastikan hanya proses angka valid
        if (!is_numeric($number)) {
            return '0';
        }

        // Ubah ke float secara eksplisit
        $number = (float) $number;

        $suffix = '';
        $formattedNumber = $number;

        if ($number < 900) {
            $formattedNumber = $number;
        } elseif ($number < 900000) {
            $formattedNumber = $number / 1000;
            $suffix = 'rb';
        } elseif ($number < 900000000) {
            $formattedNumber = $number / 1000000;
            $suffix = 'jt';
        } elseif ($number < 900000000000) {
            $formattedNumber = $number / 1000000000;
            $suffix = 'M';
        } else {
            $formattedNumber = $number / 1000000000000;
            $suffix = 'T';
        }

        // Pastikan formattedNumber bertipe float sebelum dipakai
        $formattedNumber = (float) $formattedNumber;

        // Hilangkan ".00" jika bilangan bulat
        $n_format = ($formattedNumber == floor($formattedNumber))
            ? number_format($formattedNumber, 0)
            : number_format($formattedNumber, $precision);

        return $n_format . $suffix;
    }
}
