<?php

namespace App\Actions\Parent;

use App\Models\ParentUser;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateParentAction
{
    public function exectute(array $data): ParentUser
    {
        $userData = Arr::only($data, [
            'email',
            'phone',
            'password',
        ]);

        $userData['password'] = Hash::make($userData['password']);

        $parentData = Arr::only($data, [
            'first_name',
            'last_name',
            'street',
            'house_number',
            'apartment_number',
            'city',
            'postal_code',
        ]);

        $children = $data['children'] ?? [];

        return DB::transaction(function () use ($userData, $parentData, $children) {
            $user = User::create($userData);

            $parent = ParentUser::create([
                ...$parentData,
                'user_id' => $user->id,
            ]);

            $parent->children()->sync($children);

            return $parent;
        });

    }
}
