<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class UserService extends Service {
    /*
    |--------------------------------------------------------------------------
    | User Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of users.
    |
    */

    /**
     * Create a user.
     *
     * @param array $data
     *
     * @return User
     */
    public function createUser($data) {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }

    /**
     * Updates a user. Used in modifying the admin user on the command line.
     *
     * @param array $data
     *
     * @return User
     */
    public function updateUser($data) {
        $user = User::find($data['id']);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        if ($user) {
            $user->update($data);
        }

        return $user;
    }

    /**
     * Updates the user's password.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updatePassword($data, $user) {
        DB::beginTransaction();

        try {
            if (!Hash::check($data['old_password'], $user->password)) {
                throw new \Exception('Please enter your old password.');
            }
            if (Hash::make($data['new_password']) == $user->password) {
                throw new \Exception('Please enter a different password.');
            }

            $user->password = Hash::make($data['new_password']);
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's email and resends a verification email.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updateEmail($data, $user) {
        if (User::where('email', $data['email'])->where('id', '!=', $user->id)->first()) {
            throw new \Exception('A user with this email address already exists.');
        }

        $user->email = $data['email'];
        $user->save();

        return true;
    }

    /**
     * Confirms a user's two-factor auth.
     *
     * @param string $code
     * @param array  $data
     * @param User   $user
     *
     * @return bool
     */
    public function confirmTwoFactor($code, $data, $user) {
        DB::beginTransaction();

        try {
            if (app(TwoFactorAuthenticationProvider::class)->verify(decrypt($data['two_factor_secret']), $code['code'])) {
                $user->forceFill([
                    'two_factor_secret'         => $data['two_factor_secret'],
                    'two_factor_recovery_codes' => $data['two_factor_recovery_codes'],
                ])->save();
            } else {
                throw new \Exception('Provided code was invalid.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Disables a user's two-factor auth.
     *
     * @param string $code
     * @param User   $user
     *
     * @return bool
     */
    public function disableTwoFactor($code, $user) {
        DB::beginTransaction();

        try {
            if (app(TwoFactorAuthenticationProvider::class)->verify(decrypt($user->two_factor_secret), $code['code'])) {
                $user->forceFill([
                    'two_factor_secret'         => null,
                    'two_factor_recovery_codes' => null,
                ])->save();
            } else {
                throw new \Exception('Provided code was invalid.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
