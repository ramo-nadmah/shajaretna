<?php

namespace App\Console\Commands;

use App\Models\Person;
use App\Services\KinshipCalculator;
use Illuminate\Console\Command;

class TestKinship extends Command
{
    protected $signature   = 'kinship:test';
    protected $description = 'Test the KinshipCalculator against known relationships';

    public function handle(KinshipCalculator $calculator): void
    {
        $people = Person::all()->keyBy('id');

        $tests = [
            [10, 10, 'نفس الشخص'],
            [10,  4, 'أبوه'],
            [10,  2, 'جدته من أبيه'],
            [10,  1, 'جده من أبيه'],
            [10,  5, 'عمته'],
            [10,  6, 'عمه'],
            [10, 11, 'بنت عمته'],
            [10, 12, 'ابن عمه'],
            [ 4, 10, 'ابنه'],
            [ 1, 10, 'حفيده'],
            [11, 12, 'ابن خالها'],
            [10, 14, 'ابنه'],
            [ 1, 14, 'ابن حفيده'],
            [ 2, 14, 'ابن حفيدها'],
            // Double marriage-extended: Said ↔ Sara — Sara is Ahmad's عمة — Ahmad ↔ Nadia
            [ 9, 13, 'زوجة ابن أخي زوجته'],  // Said → Nadia
            [13,  9, 'زوج عمة زوجها'],         // Nadia → Said
        ];

        $this->table(
            ['A', 'B', 'نتيجة الحاسبة', 'المتوقع', 'نجح؟'],
            collect($tests)->map(function (array $test) use ($calculator, $people) {
                [$personAId, $personBId, $expectedLabel] = $test;
                $personA = $people[$personAId];
                $personB = $people[$personBId];
                $result  = $calculator->calculate($personA, $personB);
                $passed  = $result->arabicLabel === $expectedLabel ? '✅' : '❌';

                return [
                    $personA->name_ar,
                    $personB->name_ar,
                    $result->arabicLabel,
                    $expectedLabel,
                    $passed,
                ];
            })->toArray()
        );
    }
}
