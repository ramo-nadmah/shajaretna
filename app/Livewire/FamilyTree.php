<?php

namespace App\Livewire;

use App\Enums\ParentType;
use App\Models\Marriage;
use App\Models\ParentChild;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tree')]
#[Title('شجرة العائلة — شجرتنا')]
class FamilyTree extends Component
{
    public function linkAsParent(int $parentId, int $childId, string $parentType): void
    {
        if (!\in_array($parentType, ['father', 'mother'])) return;
        if (!Person::where('id', $parentId)->exists()) return;
        if (!Person::where('id', $childId)->exists()) return;

        $type    = ParentType::from($parentType);
        $already = ParentChild::where('child_id', $childId)
            ->where('parent_type', $type->value)
            ->exists();
        if ($already) return;

        // Block if proposedParent is married to any descendant of proposedChild.
        // (e.g. Said married to Sara, Sara is Ibrahim's daughter → Said cannot be Ibrahim's father)
        if ($this->isMarriedToDescendantOf($parentId, $childId)) return;

        // Block if proposedParent already has a child who is married to proposedChild.
        // (e.g. Ibrahim is Sara's father, Sara married Said → Ibrahim cannot be Said's father)
        if ($this->isParentOfSpouseOf($parentId, $childId)) return;

        // Block if the proposed parent and the child's existing co-parent are within
        // prohibited blood kinship — adding this parent would imply an incestuous pair.
        // (e.g. Yousef's father is Omar; Laila is Omar's niece → Laila cannot be Yousef's mother)
        $coParentType = $type === ParentType::Father ? ParentType::Mother->value : ParentType::Father->value;
        $coParent     = ParentChild::where('child_id', $childId)
            ->where('parent_type', $coParentType)
            ->first();
        if ($coParent && $this->isWithinProhibitedKinship($parentId, $coParent->parent_id)) return;

        ParentChild::create([
            'parent_id'   => $parentId,
            'child_id'    => $childId,
            'parent_type' => $type,
            'created_by'  => auth()->id(),
        ]);
    }

    private function isMarriedToDescendantOf(int $parentId, int $childId): bool
    {
        $descendants = [];
        $queue       = [$childId];
        $visited     = [];
        while (\count($queue) > 0) {
            $current = \array_shift($queue);
            if (isset($visited[$current])) continue;
            $visited[$current] = true;
            $children = ParentChild::where('parent_id', $current)->pluck('child_id');
            foreach ($children as $descendantId) {
                if (!isset($visited[$descendantId])) {
                    $descendants[] = $descendantId;
                    $queue[]       = $descendantId;
                }
            }
        }
        if ($descendants === []) return false;

        return Marriage::where(function ($q) use ($parentId, $descendants) {
            $q->where('husband_id', $parentId)->whereIn('wife_id', $descendants);
        })->orWhere(function ($q) use ($parentId, $descendants) {
            $q->where('wife_id', $parentId)->whereIn('husband_id', $descendants);
        })->exists();
    }

    private function isParentOfSpouseOf(int $parentId, int $childId): bool
    {
        $childrenOfParent = ParentChild::where('parent_id', $parentId)->pluck('child_id')->toArray();
        if ($childrenOfParent === []) return false;

        return Marriage::where(function ($q) use ($childId, $childrenOfParent) {
            $q->where('husband_id', $childId)->whereIn('wife_id', $childrenOfParent);
        })->orWhere(function ($q) use ($childId, $childrenOfParent) {
            $q->where('wife_id', $childId)->whereIn('husband_id', $childrenOfParent);
        })->exists();
    }

    public function linkAsSpouse(int $husbandId, int $wifeId): void
    {
        if (!Person::where('id', $husbandId)->exists()) return;
        if (!Person::where('id', $wifeId)->exists()) return;

        $already = Marriage::where('husband_id', $husbandId)
            ->where('wife_id', $wifeId)
            ->exists();
        if ($already) return;

        if ($this->isWithinProhibitedKinship($husbandId, $wifeId)) return;

        Marriage::create([
            'husband_id' => $husbandId,
            'wife_id'    => $wifeId,
            'created_by' => auth()->id(),
        ]);
    }

    private function isWithinProhibitedKinship(int $idA, int $idB): bool
    {
        // Block if min(distA_to_LCA, distB_to_LCA) ≤ 1 for any common ancestor.
        // min=1 covers siblings, aunts/uncles at any generational depth, nieces/nephews.
        // min=2 (first cousins, 2+2) is explicitly allowed per Islamic/Arab tradition.
        $distFromA = [$idA => 0];
        $queue     = [[$idA, 0]];
        $visited   = [];
        while (\count($queue) > 0) {
            [$current, $depth] = \array_shift($queue);
            if (isset($visited[$current])) continue;
            $visited[$current] = true;
            foreach (ParentChild::where('child_id', $current)->pluck('parent_id') as $parentId) {
                if (!isset($distFromA[$parentId])) {
                    $distFromA[$parentId] = $depth + 1;
                    $queue[] = [$parentId, $depth + 1];
                }
            }
        }
        $distFromB = [$idB => 0];
        $queue     = [[$idB, 0]];
        $visited   = [];
        while (\count($queue) > 0) {
            [$current, $depth] = \array_shift($queue);
            if (isset($visited[$current])) continue;
            $visited[$current] = true;
            foreach (ParentChild::where('child_id', $current)->pluck('parent_id') as $parentId) {
                if (!isset($distFromB[$parentId])) {
                    $distFromB[$parentId] = $depth + 1;
                    $queue[] = [$parentId, $depth + 1];
                }
            }
        }
        foreach ($distFromA as $nodeId => $dA) {
            if (isset($distFromB[$nodeId]) && \min($dA, $distFromB[$nodeId]) <= 1) {
                return true;
            }
        }
        return false;
    }

    public function render()
    {
        $people = Person::all()->map(fn ($p) => [
            'id'     => $p->id,
            'name'   => $p->name_ar,
            'gender' => $p->gender->value,
        ]);

        $parentChild = ParentChild::all()->map(fn ($pc) => [
            'parent_id' => $pc->parent_id,
            'child_id'  => $pc->child_id,
            'type'      => $pc->parent_type->value,
        ]);

        $marriages = Marriage::all()->map(fn ($m) => [
            'husband_id' => $m->husband_id,
            'wife_id'    => $m->wife_id,
        ]);

        return view('livewire.family-tree', [
            'people'      => $people,
            'parentChild' => $parentChild,
            'marriages'   => $marriages,
        ]);
    }
}
