<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptsRouteKey
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return strtr(Crypt::encryptString($this->getKey()), '+/', '-_');
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString(strtr($value, '-_', '+/'));

            return $this->where($field ?? $this->getRouteKeyName(), $decrypted)->firstOrFail();
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
