<?php

namespace App\Actions\Parent;

use App\Models\ParentUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateParentAction
{
    public function exectute(ParentUser $parentUser, array $data): void
    {
        $userData = Arr::only($data, [
            'email',
            'phone',
            'password',
        ]);

        if (! empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }

        $parentData = Arr::only($data, [
            'first_name',
            'last_name',
            'street',
            'house_number',
            'apartment_number',
            'city',
            'postal_code',
        ]);

        DB::transaction(function () use ($parentUser, $userData, $parentData, $data): void {
            $parentUser->update($parentData);
            $parentUser->user()->update($userData);

            if (array_key_exists('children', $data)) {
                $parentUser->children()->sync($data['children']);
            }

        });
    }
}
