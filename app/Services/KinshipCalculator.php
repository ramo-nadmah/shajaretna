<?php

namespace App\Services;

use App\Data\KinshipResult;
use App\Enums\Gender;
use App\Enums\ParentType;
use App\Models\Person;

class KinshipCalculator
{
    private array $personCache = [];

    public function calculate(Person $personA, Person $personB): KinshipResult
    {
        $this->personCache                = [];
        $this->personCache[$personA->id]  = $personA;
        $this->personCache[$personB->id]  = $personB;

        if ($personA->id === $personB->id) {
            return new KinshipResult(arabicLabel: 'نفس الشخص', arabicLabels: ['نفس الشخص'], relationshipFound: true);
        }

        $aMale   = $personA->gender === Gender::Male;
        $bIsMale = $personB->gender === Gender::Male;

        [$pathFromA, $pathFromB] = $this->findLCA($personA->id, $personB->id);

        if ($pathFromA === null) {
            return $this->tryMarriageExtended($personA, $personB, $aMale, $bIsMale);
        }

        $nUp   = \count($pathFromA);
        $mDown = \count($pathFromB);

        $labelAB = $this->buildLabel($nUp, $mDown, $pathFromA, $pathFromB, $bIsMale, $aMale, $personA->id, $personB->id);
        $labelBA = $this->buildLabel($mDown, $nUp, $pathFromB, $pathFromA, $aMale, $bIsMale, $personB->id, $personA->id);

        $labels = [$labelAB];
        if ($labelBA !== $labelAB) {
            $labels[] = $labelBA;
        }

        return new KinshipResult(arabicLabel: $labelAB, arabicLabels: $labels, relationshipFound: true);
    }

    // -------------------------------------------------------------------------
    // LCA finder
    // -------------------------------------------------------------------------

    private function findLCA(int $aId, int $bId): array
    {
        $ancestorsOfA       = $this->getAncestors($aId);
        $ancestorsOfA[$aId] = [];
        $ancestorsOfB       = $this->getAncestors($bId);
        $ancestorsOfB[$bId] = [];

        $sharedIds = \array_keys(\array_intersect_key($ancestorsOfA, $ancestorsOfB));
        if ($sharedIds === []) {
            return [null, null];
        }

        $pathFromA = $pathFromB = [];
        $shortest  = \PHP_INT_MAX;
        foreach ($sharedIds as $ancestorId) {
            $pA   = $ancestorsOfA[$ancestorId];
            $pB   = $ancestorsOfB[$ancestorId];
            $dist = \count($pA) + \count($pB);
            if ($dist < $shortest) {
                $shortest  = $dist;
                $pathFromA = $pA;
                $pathFromB = $pB;
            }
        }

        return [$pathFromA, $pathFromB];
    }

    // -------------------------------------------------------------------------
    // Core label builder — 3rd person, path-based
    // pathFromA = steps from A up to LCA: each step = ['id'=>int, 'via'=>ParentType]
    // pathFromB = steps from B up to LCA (same structure)
    // -------------------------------------------------------------------------

    private function buildLabel(
        int   $nUp,
        int   $mDown,
        array $pathFromA,
        array $pathFromB,
        bool  $bIsMale,
        bool  $aMale,
        int   $aId,
        int   $bId
    ): string {
        $p = $aMale ? 'ه' : 'ها';

        // B is A's ancestor
        if ($mDown === 0) {
            return $this->ancestorLabel($nUp, $pathFromA, $bIsMale, $aMale);
        }

        // B is A's descendant
        if ($nUp === 0) {
            return $this->descendantLabel($mDown, $bIsMale, $aMale);
        }

        // Sibling
        if ($nUp === 1 && $mDown === 1) {
            $viaFather = !empty($pathFromA) && $pathFromA[0]['via'] === ParentType::Father;
            return $this->siblingLabel($viaFather, $bIsMale, $aMale, $aId, $bId);
        }

        // A's sibling's descendants (nUp=1, mDown>=2)
        if ($nUp === 1) {
            $sibId       = $pathFromB[$mDown - 2]['id'] ?? null;
            $sib         = $sibId ? $this->loadPerson($sibId) : null;
            $sibMale     = $sib?->gender === Gender::Male ?? true;
            $sibGenitive = $sibMale
                ? ($aMale ? 'أخيه' : 'أخيها')
                : ($aMale ? 'أخته' : 'أختها');

            $prefix = $bIsMale
                ? \str_repeat('ابن ', $mDown - 1)
                : 'بنت ' . ($mDown >= 3 ? \str_repeat('ابن ', $mDown - 2) : '');
            return \trim($prefix . $sibGenitive);
        }

        // nUp >= 2: uncle/aunt and cousin direction
        // Uncle type (عم vs خال) is determined by pathFromA[nUp-2] — the SECOND-TO-LAST step,
        // i.e. A's first-ancestor's relationship tells us paternal vs maternal side.
        $lcaViaFather = $pathFromA[\count($pathFromA) - 2]['via'] === ParentType::Father;

        if ($mDown === 1) {
            $uncleIsMale = $bIsMale;
        } else {
            $uncleId     = $pathFromB[$mDown - 2]['id'] ?? null;
            $uncle       = $uncleId ? $this->loadPerson($uncleId) : null;
            $uncleIsMale = $uncle?->gender === Gender::Male ?? true;
        }

        // Female nouns: ta marbuta (ة) must become ta (ت) before possessive suffix
        if ($uncleIsMale) {
            $uncleForSuffix  = $lcaViaFather ? 'عم' : 'خال';
            $uncleStandalone = $uncleForSuffix;
        } else {
            $uncleForSuffix  = $lcaViaFather ? 'عمت' : 'خالت';
            $uncleStandalone = $lcaViaFather ? 'عمة' : 'خالة';
        }

        $uncleLabel = ($nUp === 2)
            ? $uncleForSuffix . $p
            : $uncleStandalone . ' ' . $this->buildAncestorSuffix($pathFromA, $aMale);

        if ($mDown === 1) {
            return $uncleLabel;
        }

        $prefix = $bIsMale
            ? \str_repeat('ابن ', $mDown - 1)
            : 'بنت ' . ($mDown >= 3 ? \str_repeat('ابن ', $mDown - 2) : '');
        return \trim($prefix . $uncleLabel);
    }

    // -------------------------------------------------------------------------
    // Ancestor label — B is A's ancestor nUp steps above
    // -------------------------------------------------------------------------

    private function ancestorLabel(int $nUp, array $pathFromA, bool $bIsMale, bool $aMale): string
    {
        $p         = $aMale ? 'ه' : 'ها';
        $step0Via  = !empty($pathFromA) ? $pathFromA[0]['via'] : ParentType::Father;
        $viaFather = ($step0Via === ParentType::Father);

        if ($nUp === 1) {
            return $viaFather ? "أبو$p" : "أم$p";
        }

        if ($nUp === 2) {
            $base      = $bIsMale ? "جد$p" : ($aMale ? 'جدته' : 'جدتها');
            $qualifier = $viaFather
                ? (' من ' . ($aMale ? 'أبيه' : 'أبيها'))
                : (' من ' . ($aMale ? 'أمه' : 'أمها'));
            return $base . $qualifier;
        }

        if ($nUp === 3) {
            // "جد أبيه" (great-grandfather) or "جدة أبيه" (great-grandmother)
            // buildAncestorSuffix returns 'أبيه'/'أمه' for n=1, encoding the side.
            $bGender = $bIsMale ? 'جد' : 'جدة';
            $suffix  = $this->buildAncestorSuffix($pathFromA, $aMale);
            return $suffix === '' ? $bGender . $p : "$bGender $suffix";
        }

        // nUp >= 4: ordinal form — "الجد الثالث من جهة أبيه"
        // Ordinal index: nUp=4 → الثالث (grandfather-distance 3), nUp=5 → الرابع, …
        $maleOrdinals   = ['الثالث', 'الرابع', 'الخامس', 'السادس', 'السابع', 'الثامن', 'التاسع', 'العاشر'];
        $femaleOrdinals = ['الثالثة', 'الرابعة', 'الخامسة', 'السادسة', 'السابعة', 'الثامنة', 'التاسعة', 'العاشرة'];
        $ordinalIndex   = $nUp - 4;
        $bGender        = $bIsMale ? 'الجد' : 'الجدة';
        $ordinal        = $bIsMale
            ? ($maleOrdinals[$ordinalIndex] ?? '')
            : ($femaleOrdinals[$ordinalIndex] ?? '');
        $sideWord       = $viaFather
            ? ($aMale ? 'أبيه' : 'أبيها')
            : ($aMale ? 'أمه' : 'أمها');

        if ($ordinal === '') {
            // Fallback for ancestors beyond 11 generations
            return ($bIsMale ? 'جد ' : 'جدة ') . \str_repeat('جد ', $nUp - 2) . "جد$p";
        }

        return "$bGender $ordinal من جهة $sideWord";
    }

    // -------------------------------------------------------------------------
    // Descendant label — B is A's descendant mDown steps below
    // -------------------------------------------------------------------------

    private function descendantLabel(int $mDown, bool $bIsMale, bool $aMale): string
    {
        $p = $aMale ? 'ه' : 'ها';

        if ($mDown === 1) return $bIsMale ? "ابن$p" : ($aMale ? 'بنته' : 'بنتها');
        if ($mDown === 2) return $bIsMale ? "حفيد$p" : ($aMale ? 'حفيدته' : 'حفيدتها');
        if ($mDown === 3) return $bIsMale ? "ابن حفيد$p" : ($aMale ? 'بنت حفيدته' : 'بنت حفيدتها');

        $prefix = $bIsMale
            ? \str_repeat('ابن ', $mDown - 2)
            : 'بنت ' . \str_repeat('ابن ', $mDown - 3);
        return \trim($prefix) . " حفيد$p";
    }

    // -------------------------------------------------------------------------
    // Sibling label — distinguishes full vs half-siblings
    // -------------------------------------------------------------------------

    private function siblingLabel(bool $viaFather, bool $bIsMale, bool $aMale, int $aId, int $bId): string
    {
        $p    = $aMale ? 'ه' : 'ها';
        $base = $bIsMale ? "أخو$p" : ($aMale ? 'أخته' : 'أختها');

        if (!$viaFather) {
            return $base . " من أم$p";
        }

        // Shared father — only flag half-sibling if BOTH have mothers recorded AND they differ
        $personA      = $this->loadPerson($aId);
        $personB      = $this->loadPerson($bId);
        $aMotherLink  = $personA?->parentChildAsChild->first(fn($l) => $l->parent_type === ParentType::Mother);
        $bMotherLink  = $personB?->parentChildAsChild->first(fn($l) => $l->parent_type === ParentType::Mother);
        $diffMothers  = $aMotherLink && $bMotherLink && $aMotherLink->parent_id !== $bMotherLink->parent_id;

        return $diffMothers ? $base . " من أبي$p" : $base;
    }

    // -------------------------------------------------------------------------
    // Ancestor suffix — describes A's ancestor (nUp-2) levels above A.
    // Used as the tail of a lateral kinship chain: عم [suffix] / خال [suffix].
    // Returns '' for nUp=2 (possessive goes on the lateral term directly).
    //
    // Formula (n = nUp-2 levels to describe):
    //   n=1  → أبيه / أمه          (A's direct parent)
    //   n=2  → جده / جدته          (A's grandparent; + لأمه if via maternal step)
    //   n=3  → جد أبيه             (1 جد prefix + أبيه)
    //   n=4  → جد جده              (1 جد prefix + جده)
    //   n=5  → جد جد أبيه          (2 جد prefixes + أبيه)
    //   n≥3  → intdiv(n-1,2) × "جد " + inner (أبيه if n odd, جده if n even)
    // -------------------------------------------------------------------------

    private function buildAncestorSuffix(array $pathFromA, bool $aMale): string
    {
        $nUp = \count($pathFromA);
        if ($nUp <= 2) return '';

        $p        = $aMale ? 'ه' : 'ها';
        $n        = $nUp - 2;
        $step0Via = $pathFromA[0]['via'];
        $step1Via = isset($pathFromA[1]) ? $pathFromA[1]['via'] : ParentType::Father;

        if ($n === 1) {
            return ($step0Via === ParentType::Father) ? 'أبي' . $p : 'أم' . $p;
        }

        $jadCount = \intdiv($n - 1, 2);

        if ($n % 2 === 1) {
            // Odd n: inner is A's direct parent
            $inner = ($step0Via === ParentType::Father) ? 'أبي' . $p : 'أم' . $p;
        } else {
            // Even n: inner is A's grandparent (gender from step1.via)
            $grandparentMale = ($step1Via === ParentType::Father);
            $inner = $grandparentMale ? 'جد' . $p : 'جدت' . $p;
            if ($step0Via === ParentType::Mother) {
                $inner .= ' لأم' . $p;
            }
        }

        return \rtrim(\str_repeat('جد ', $jadCount) . $inner);
    }

    // -------------------------------------------------------------------------
    // Marriage-extended kinship — when no blood relation exists
    // -------------------------------------------------------------------------

    private function tryMarriageExtended(Person $personA, Person $personB, bool $aMale, bool $bIsMale): KinshipResult
    {
        $p = $aMale ? 'ه' : 'ها';

        // Direct marriage: A and B are each other's spouses
        foreach ($this->getSpouses($personA) as $spouse) {
            if ($spouse->id === $personB->id) {
                $label = $bIsMale ? "زوج$p" : "زوجت$p";
                return new KinshipResult(arabicLabel: $label, arabicLabels: [$label], relationshipFound: true);
            }
        }

        // B is married to a blood relative of A (skip A itself to avoid zero-depth loops)
        foreach ($this->getSpouses($personB) as $spouseOfB) {
            if ($spouseOfB->id === $personA->id) continue;

            [$pathFromA, $pathFromSpouse] = $this->findLCA($personA->id, $spouseOfB->id);
            if ($pathFromA === null) continue;

            $nUp          = \count($pathFromA);
            $mDown        = \count($pathFromSpouse);
            $spouseIsMale = $spouseOfB->gender === Gender::Male;
            $spouseLabel  = $this->buildLabel($nUp, $mDown, $pathFromA, $pathFromSpouse, $spouseIsMale, $aMale, $personA->id, $spouseOfB->id);

            // أب and أخ are from الأسماء الستة — nominative (أبو/أخو) must become
            // genitive (أبي/أخي) when they are the second term of an iḍāfa chain.
            $spouseLabel  = \str_replace(
                ['أبوه', 'أبوها', 'أخوه', 'أخوها'],
                ['أبيه', 'أبيها', 'أخيه', 'أخيها'],
                $spouseLabel
            );

            $label = $bIsMale ? "زوج $spouseLabel" : "زوجة $spouseLabel";

            return new KinshipResult(arabicLabel: $label, arabicLabels: [$label], relationshipFound: true);
        }

        // A is married to a blood relative of B (skip B itself)
        foreach ($this->getSpouses($personA) as $spouseOfA) {
            if ($spouseOfA->id === $personB->id) continue;

            [$pathFromSpouse, $pathFromB] = $this->findLCA($spouseOfA->id, $personB->id);
            if ($pathFromSpouse === null) continue;

            $nUp          = \count($pathFromSpouse);
            $mDown        = \count($pathFromB);
            $spouseIsMale = $spouseOfA->gender === Gender::Male;

            // buildLabel uses $spouseIsMale as $aMale, so the label carries the
            // spouse's possessive pronoun ("عمه"/"عمها"). We strip it before
            // combining with "$spouseWord" so the genitive chain reads correctly:
            // "ابن عم زوجها" not "ابن عمه زوجها".
            $bRelLabel  = $this->buildLabel($nUp, $mDown, $pathFromSpouse, $pathFromB, $bIsMale, $spouseIsMale, $spouseOfA->id, $personB->id);
            $spouseWord = $spouseIsMale ? "زوج$p" : "زوجت$p";

            // Trailing qualifier (half-sibling "من أبيه" or grandparent-side "من جهة أبيه")
            // must follow the spouse word, not precede it. Strip it before toConstructState
            // so the suffix-stripping logic sees only the bare kinship noun.
            $trailQualifier = '';
            foreach ([' من جهة أبيه', ' من جهة أبيها', ' من جهة أمه', ' من جهة أمها', ' من أبيه', ' من أبيها', ' من أمه', ' من أمها'] as $qualifier) {
                if (\str_ends_with($bRelLabel, $qualifier)) {
                    $trailQualifier = $qualifier;
                    $bRelLabel      = \substr($bRelLabel, 0, -\strlen($qualifier));
                    break;
                }
            }

            $bRelLabel = $this->toConstructState($bRelLabel, $spouseIsMale);
            $label     = "$bRelLabel $spouseWord$trailQualifier";

            return new KinshipResult(arabicLabel: $label, arabicLabels: [$label], relationshipFound: true);
        }

        // A's spouse is blood-related to B's spouse (double marriage-extended).
        // Neither A nor B has a blood relation to the other or to the other's spouse.
        // Example: Said ↔ Sara — Sara is Ahmad's عمة — Ahmad ↔ Nadia
        //   Said → Nadia : "زوجة ابن أخي زوجته" (wife of the son of his wife's brother)
        //   Nadia → Said : "زوج عمة زوجها"       (husband of the aunt of her husband)
        // Label structure: [B's role to B's spouse] + [B's spouse relative to A's spouse] + [A's spouse relative to A]
        foreach ($this->getSpouses($personA) as $spouseOfA) {
            if ($spouseOfA->id === $personB->id) continue;
            foreach ($this->getSpouses($personB) as $spouseOfB) {
                if ($spouseOfB->id === $personA->id) continue;
                if ($spouseOfA->id === $spouseOfB->id) continue; // shared spouse edge-case

                // Blood label: spouseOfB relative to spouseOfA
                // spouseOfA is "A" in the sub-call (reference), spouseOfB is "B" (target)
                [$pathFromSpouseA, $pathFromSpouseB] = $this->findLCA($spouseOfA->id, $spouseOfB->id);
                if ($pathFromSpouseA === null) continue; // no blood relation between the two spouses

                $nUp           = \count($pathFromSpouseA);
                $mDown         = \count($pathFromSpouseB);
                $spouseAIsMale = $spouseOfA->gender === Gender::Male;
                $spouseBIsMale = $spouseOfB->gender === Gender::Male;

                $bloodLabel = $this->buildLabel(
                    $nUp, $mDown,
                    $pathFromSpouseA, $pathFromSpouseB,
                    $spouseBIsMale,   // bIsMale: spouseOfB is the "B" in this sub-call
                    $spouseAIsMale,   // aMale:   spouseOfA is the "A" in this sub-call
                    $spouseOfA->id,
                    $spouseOfB->id
                );

                // Trailing qualifier must follow the A-spouse word, not precede it
                $trailQualifier = '';
                foreach ([' من جهة أبيه', ' من جهة أبيها', ' من جهة أمه', ' من جهة أمها', ' من أبيه', ' من أبيها', ' من أمه', ' من أمها'] as $qualifier) {
                    if (\str_ends_with($bloodLabel, $qualifier)) {
                        $trailQualifier = $qualifier;
                        $bloodLabel     = \substr($bloodLabel, 0, -\strlen($qualifier));
                        break;
                    }
                }

                // bloodConstruct is the middle iḍāfa term: strip spouseOfA's possessive suffix
                $bloodConstruct = $this->toConstructState($bloodLabel, $spouseAIsMale);

                // الأسماء الستة: أبو/أخو in middle-chain position (مضاف إليه) need genitive أبي/أخي
                $bloodConstruct = \str_replace(['أبو', 'أخو'], ['أبي', 'أخي'], $bloodConstruct);

                // B's role relative to B's spouse (outer/first term — what B is to spouseOfB)
                $bRoleForBsSpouse = $spouseBIsMale ? 'زوجة' : 'زوج';

                // A's spouse relative to A (inner/tail term)
                $aSuffix     = $aMale ? 'ه' : 'ها';
                $aSpouseWord = $spouseAIsMale ? "زوج$aSuffix" : "زوجت$aSuffix";

                $label = "$bRoleForBsSpouse $bloodConstruct $aSpouseWord$trailQualifier";

                return new KinshipResult(arabicLabel: $label, arabicLabels: [$label], relationshipFound: true);
            }
        }

        return new KinshipResult(arabicLabel: 'لا توجد صلة قرابة مسجلة', arabicLabels: [], relationshipFound: false);
    }

    // Strips the possessive pronoun that buildLabel appended for the spouse's
    // gender, returning the construct-state form used in iḍāfa chains.
    // Examples: "ابن عمه" → "ابن عم", "عمته" → "عمة", "أبوه" → "أبو".
    private function toConstructState(string $label, bool $referenceMale): string
    {
        $suffix = $referenceMale ? 'ه' : 'ها';

        if (\str_ends_with($label, $suffix)) {
            $label = \substr($label, 0, -\strlen($suffix));
        }

        // ta marbuta (ة) was changed to ta (ت) before the suffix; restore it.
        // Full list of words in this system whose ة becomes ت before a suffix:
        //   عمة، خالة، زوجة، حفيدة، جدة
        // Note: بنت and أخت end in genuine ت (not ة) and must NOT be changed.
        return \str_replace(
            ['عمت', 'خالت', 'زوجت', 'حفيدت', 'جدت'],
            ['عمة', 'خالة', 'زوجة', 'حفيدة', 'جدة'],
            $label
        );
    }

    private function getSpouses(Person $person): array
    {
        $person->loadMissing(['marriagesAsHusband', 'marriagesAsWife']);
        $spouses = [];
        foreach ($person->marriagesAsHusband as $marriage) {
            if ($spouse = $this->loadPerson($marriage->wife_id)) $spouses[] = $spouse;
        }
        foreach ($person->marriagesAsWife as $marriage) {
            if ($spouse = $this->loadPerson($marriage->husband_id)) $spouses[] = $spouse;
        }
        return $spouses;
    }

    // -------------------------------------------------------------------------
    // BFS upward — returns [ancestorId => pathToThatAncestor]
    // -------------------------------------------------------------------------

    private function getAncestors(int $personId): array
    {
        $ancestorPaths = [];
        $queue         = [[$personId, []]];
        $visited       = [$personId => true];

        while ($queue !== []) {
            [$currentId, $currentPath] = \array_shift($queue);
            $currentPerson = $this->loadPerson($currentId);
            if (!$currentPerson) continue;

            foreach ($currentPerson->parentChildAsChild as $parentLink) {
                $parentId = $parentLink->parent_id;
                if (isset($visited[$parentId])) continue;
                $visited[$parentId] = true;

                $pathToParent             = [...$currentPath, ['id' => $parentId, 'via' => $parentLink->parent_type]];
                $ancestorPaths[$parentId] = $pathToParent;
                $queue[]                  = [$parentId, $pathToParent];
            }
        }

        return $ancestorPaths;
    }

    private function loadPerson(int $id): ?Person
    {
        if (!isset($this->personCache[$id])) {
            $this->personCache[$id] = Person::with(['parentChildAsChild', 'marriagesAsHusband', 'marriagesAsWife'])->find($id);
        }
        return $this->personCache[$id];
    }
}
