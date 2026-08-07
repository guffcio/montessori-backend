<?php

namespace App\Actions\Child;

use App\Models\Child;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateChildAction
{
    public function execute(Child $child, array $data): void
    {

        $childData = Arr::only(
            $data,
            [
                'first_name',
                'last_name',
                'birth_date',
                'zone_id',
                'pesel',
                'started_at',
                'preschool_started_at',
            ]
        );

        DB::transaction(function () use ($child, $childData, $data) {

            $child->update($childData);

            if (array_key_exists('parents', $data)) {
                $child->parents()->sync($data['parents']);
            }

            if (array_key_exists('allergens', $data)) {
                $child->allergens()->sync($data['allergens']);
            }

        });
    }
}
