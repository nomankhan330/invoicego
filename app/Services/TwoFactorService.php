<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Pure-PHP RFC 6238 TOTP Two-Factor Authentication service.
 * Compatible with Google Authenticator, Microsoft Authenticator, Authy, and 1Password.
 */
class TwoFactorService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $secret = '';
        $bytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[ord($bytes[$i]) & 31];
        }

        return $secret;
    }

    public function getProvisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            rawurlencode($secret),
            rawurlencode($issuer)
        );
    }

    public function getCode(string $secret, ?int $timestamp = null, int $period = 30, int $digits = 6): string
    {
        $timestamp ??= time();
        $counter = (int) floor($timestamp / $period);

        $binaryCounter = pack('N*', 0, $counter);
        $key = $this->base32Decode($secret);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $truncated = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $truncated % (10 ** $digits);

        return str_pad((string) $otp, $digits, '0', STR_PAD_LEFT);
    }

    public function verifyCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $normalized = preg_replace('/\s+/', '', trim($code));
        if (!preg_match('/^\d{6}$/', $normalized)) {
            return false;
        }

        $timestamp ??= time();

        for ($offset = -$window; $offset <= $window; $offset++) {
            $expected = $this->getCode($secret, $timestamp + ($offset * 30));
            if (hash_equals($expected, $normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate cryptographically random single-use recovery codes (e.g. ABCD-EFGH).
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(Str::random(4));
            $part2 = strtoupper(Str::random(4));
            $codes[] = "{$part1}-{$part2}";
        }

        return $codes;
    }

    public function formatSecretForDisplay(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $secret));
        $alphabet = self::BASE32_ALPHABET;

        $bits = '';
        $length = strlen($secret);
        for ($i = 0; $i < $length; $i++) {
            $val = strpos($alphabet, $secret[$i]);
            if ($val === false) {
                continue;
            }
            $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $binary .= chr((int) bindec($byte));
            }
        }

        return $binary;
    }
}
