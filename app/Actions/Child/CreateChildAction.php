<?php

namespace App\Actions\Child;

use App\Models\Child;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateChildAction
{
    public function execute(array $data): Child
    {

        $childData = Arr::only($data, [
            'first_name',
            'last_name',
            'birth_date',
            'zone_id',
            'pesel',
            'started_at',
            'preschool_started_at',
        ]);

        $parents = $data['parents'] ?? [];
        $allergens = $data['allergens'] ?? [];

        return DB::transaction(function () use ($childData, $parents, $allergens) {
            $child = Child::create($childData);

            $child->parents()->sync($parents);
            $child->allergens()->sync($allergens);

            return $child;
        });
    }
}
