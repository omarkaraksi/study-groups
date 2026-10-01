<?php

namespace App\Application\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;

class RegisterApiUser
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function handle(array $attributes): NewAccessToken
    {
        return DB::transaction(function () use ($attributes): NewAccessToken {
            $user = User::query()->create($attributes);

            return $user->createToken('api');
        });
    }
}
