<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Generates human-friendly unique IDs such as APP-261107-K7M2QX or LN-261107-8HD3PA.
 * Avoids look-alike characters (0/O, 1/I) so customers can read them over the phone.
 */
class ReferenceGenerator
{
    private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function make(string $prefix, string $table, string $column = 'reference', int $length = 6): string
    {
        do {
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $code = sprintf('%s-%s-%s', $prefix, now()->format('ymd'), $random);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
