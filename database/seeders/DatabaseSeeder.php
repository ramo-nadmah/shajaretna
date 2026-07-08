<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\ParentType;
use App\Models\Marriage;
use App\Models\ParentChild;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private int $userId;

    public function run(): void
    {
        $user = User::create([
            'first_name'  => 'أحمد',
            'second_name' => 'محمد',
            'third_name'  => 'علي',
            'fourth_name' => 'الحمدان',
            'mobile'      => '0501234567',
            'created_by'  => null,
        ]);

        $this->userId = $user->id;

        /*
         * Family tree (4 generations):
         *
         *         إبراهيم ═══ مريم (wife 1)
         *         │       ╚══ نورة (wife 2)
         *         │
         *      ┌──┼────────┬──────────┐
         *   خالد  سارة   عمر(نورة)  هند(نورة)
         *     │     │       │
         *   أحمد  ليلى   يوسف
         *  (+ ريم)
         *     │
         *   فيصل
         */

        // ── Generation 1 ──────────────────────────────────────────────
        $ibrahim = $this->person('إبراهيم الحمدان', Gender::Male);
        $maryam  = $this->person('مريم الحمدان',   Gender::Female);
        $noura   = $this->person('نورة الحمدان',   Gender::Female);

        $this->marry($ibrahim, $maryam);
        $this->marry($ibrahim, $noura);

        // ── Generation 2 ──────────────────────────────────────────────
        $khalid = $this->person('خالد الحمدان', Gender::Male);
        $sara   = $this->person('سارة الحمدان', Gender::Female);
        $omar   = $this->person('عمر الحمدان',  Gender::Male);
        $hind   = $this->person('هند الحمدان',  Gender::Female);

        $this->link($ibrahim, $khalid, ParentType::Father);
        $this->link($maryam,  $khalid, ParentType::Mother);
        $this->link($ibrahim, $sara,   ParentType::Father);
        $this->link($maryam,  $sara,   ParentType::Mother);

        $this->link($ibrahim, $omar, ParentType::Father);
        $this->link($noura,   $omar, ParentType::Mother);
        $this->link($ibrahim, $hind, ParentType::Father);
        $this->link($noura,   $hind, ParentType::Mother);

        // ── Generation 3 ──────────────────────────────────────────────
        $reem  = $this->person('ريم العتيبي',  Gender::Female);
        $saeed = $this->person('سعيد المطيري', Gender::Male);

        $this->marry($khalid, $reem);
        $this->marry($saeed,  $sara);

        $ahmad  = $this->person('أحمد الحمدان', Gender::Male);
        $layla  = $this->person('ليلى المطيري', Gender::Female);
        $yousef = $this->person('يوسف الحمدان', Gender::Male);

        $this->link($khalid, $ahmad,  ParentType::Father);
        $this->link($reem,   $ahmad,  ParentType::Mother);
        $this->link($saeed,  $layla,  ParentType::Father);
        $this->link($sara,   $layla,  ParentType::Mother);
        $this->link($omar,   $yousef, ParentType::Father);

        // ── Generation 4 ──────────────────────────────────────────────
        $nadia = $this->person('نادية السلمي', Gender::Female);
        $this->marry($ahmad, $nadia);

        $faisal = $this->person('فيصل الحمدان', Gender::Male);
        $this->link($ahmad, $faisal, ParentType::Father);
        $this->link($nadia, $faisal, ParentType::Mother);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function person(string $nameAr, Gender $gender): Person
    {
        return Person::create([
            'name_ar'    => $nameAr,
            'gender'     => $gender->value,
            'created_by' => $this->userId,
        ]);
    }

    private function marry(Person $husband, Person $wife): void
    {
        Marriage::create([
            'husband_id' => $husband->id,
            'wife_id'    => $wife->id,
            'created_by' => $this->userId,
        ]);
    }

    private function link(Person $parent, Person $child, ParentType $type): void
    {
        ParentChild::create([
            'parent_id'   => $parent->id,
            'child_id'    => $child->id,
            'parent_type' => $type->value,
            'created_by'  => $this->userId,
        ]);
    }
}
