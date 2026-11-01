<?php

namespace Encore\Admin\Auth\TwoFactor;

/**
 * RFC 6238 time-based one-time passwords (HMAC-SHA1, 30 second step, 6 digits),
 * compatible with Google Authenticator, Authy, 1Password and similar apps.
 */
class Totp
{
    const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    const PERIOD = 30;

    const DIGITS = 6;

    /**
     * Generate a new base32 encoded secret.
     *
     * @param int $bytes
     *
     * @return string
     */
    public static function generateSecret($bytes = 20)
    {
        return static::base32Encode(random_bytes($bytes));
    }

    /**
     * Code for the given secret at the given unix time.
     *
     * @param string   $secret
     * @param int|null $time
     *
     * @return string
     */
    public static function code($secret, $time = null)
    {
        $counter = intdiv($time ?? time(), static::PERIOD);

        $hash = hash_hmac('sha1', pack('J', $counter), static::base32Decode($secret), true);

        $offset = ord($hash[19]) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3]);

        return str_pad((string) ($value % (10 ** static::DIGITS)), static::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a code, tolerating `$window` steps of clock drift either way.
     *
     * @param string   $secret
     * @param string   $code
     * @param int      $window
     * @param int|null $time
     *
     * @return bool
     */
    public static function verify($secret, $code, $window = 1, $time = null)
    {
        $code = preg_replace('/\s+/', '', (string) $code);

        if (!preg_match('/^\d{'.static::DIGITS.'}$/', $code)) {
            return false;
        }

        $time = $time ?? time();

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(static::code($secret, $time + $i * static::PERIOD), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The otpauth:// URI that authenticator apps read from the QR code.
     *
     * @param string $issuer
     * @param string $account
     * @param string $secret
     *
     * @return string
     */
    public static function uri($issuer, $account, $secret)
    {
        return sprintf(
            'otpauth://totp/%s:%s?%s',
            rawurlencode($issuer),
            rawurlencode($account),
            http_build_query([
                'secret'    => $secret,
                'issuer'    => $issuer,
                'algorithm' => 'SHA1',
                'digits'    => static::DIGITS,
                'period'    => static::PERIOD,
            ], '', '&', PHP_QUERY_RFC3986)
        );
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public static function base32Encode($data)
    {
        $bits = '';

        foreach (str_split($data) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';

        foreach (str_split($bits, 5) as $chunk) {
            $output .= static::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $output;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public static function base32Decode($data)
    {
        $data = strtoupper(preg_replace('/[\s=]+/', '', $data));

        $bits = '';

        foreach (str_split($data) as $char) {
            $index = strpos(static::ALPHABET, $char);

            if ($index === false) {
                continue;
            }

            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $output = '';

        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $output .= chr(bindec($byte));
            }
        }

        return $output;
    }
}
