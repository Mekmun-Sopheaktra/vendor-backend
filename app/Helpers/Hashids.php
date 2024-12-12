<?php

use Hashids\Hashids;
use Illuminate\Support\Facades\Log;
use AppHelper;

if (!function_exists('hashidsEncodeId')) {
    /**
     * Encodes an integer ID into a Hashids string.
     *
     * @param int $id The ID to encode.
     * @param int $length The minimum length of the resulting Hashids string.
     *
     * @return string|null The encoded string, or null on failure.
     */
    function hashidsEncodeId(int $id, int $length = 32): ?string
    {
        try {
            $hashids = new Hashids(config('hashing.hash_key'), $length);
            return $hashids->encode($id);
        } catch (Exception $exception) {
            Log::error('Error encoding ID with Hashids: ' . $exception->getMessage(), [
                'id' => $id,
                'length' => $length
            ]);
            return null;
        }
    }
}

if (!function_exists('hashidsDecodeId')) {
    /**
     * Decodes a Hashids string back into an integer ID.
     *
     * @param string $encoded The encoded Hashids string.
     * @param int $length The minimum length of the Hashids string (must match the encoding length).
     *
     * @return int|null The decoded ID, or null if decoding fails.
     */
    function hashidsDecodeId(string $encoded, int $length = 32): ?int
    {
        try {
            $hashids = new Hashids(config('hashing.hash_key'), $length);
            $decoded = $hashids->decode($encoded);

            return $decoded[0] ?? null;
        } catch (Exception $exception) {
            Log::error('Error decoding Hashids string: ' . $exception->getMessage(), [
                'encoded' => $encoded,
                'length' => $length
            ]);
            return null;
        }
    }
}
