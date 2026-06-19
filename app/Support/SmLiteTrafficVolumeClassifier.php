<?php

namespace App\Support;

final class SmLiteTrafficVolumeClassifier
{
    /**
     * @return array{r2:int,r4:int,r6:int,r6_plus:int,total:int,volume_class:string}
     */
    public static function classify(mixed $r2, mixed $r4, mixed $r6, mixed $jumlah = null): array
    {
        $r2v = max(0, (int) $r2);
        $r4v = max(0, (int) $r4);
        $r6Raw = max(0, (int) $r6);
        $r6 = min($r6Raw, 999_999_999);
        $r6Plus = $r6 >= 7 ? $r6 - 6 : 0;

        $computedTotal = $r2v + $r4v + $r6;
        $total = $jumlah === null ? $computedTotal : max($computedTotal, max(0, (int) $jumlah));

        $class = 'R2';
        if ($r6Plus > 0) {
            $class = 'R6+';
        } elseif ($r6 > 0) {
            $class = 'R6';
        } elseif ($r4v > 0) {
            $class = 'R4';
        }

        return [
            'r2' => $r2v,
            'r4' => $r4v,
            'r6' => $r6,
            'r6_plus' => $r6Plus,
            'total' => $total,
            'volume_class' => $class,
        ];
    }
}
