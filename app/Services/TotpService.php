<?php

namespace App\Services;

use Exception;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure random Base32 secret key.
     */
    public function generateSecret(int $length = 16): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $secret = '';
        $maxIndex = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, $maxIndex)];
        }

        return $secret;
    }

    /**
     * Decode a Base32 string into binary bytes.
     */
    public function base32Decode(string $base32): string
    {
        $base32 = strtoupper(trim($base32));
        $base32 = rtrim($base32, '=');
        $alphabet = self::BASE32_ALPHABET;

        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $val = strpos($alphabet, $char);
            if ($val === false) {
                continue; // Ignore invalid characters / spaces
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }

    /**
     * Encode binary bytes into a Base32 string.
     */
    public function base32Encode(string $data): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0; $i < strlen($data); $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $output .= $alphabet[($buffer >> $bitsLeft) & 0x1F];
            }
        }

        if ($bitsLeft > 0) {
            $buffer = $buffer << (5 - $bitsLeft);
            $output .= $alphabet[$buffer & 0x1F];
        }

        return $output;
    }

    /**
     * Calculate 6-digit TOTP code for a given secret and timestamp.
     */
    public function calculateOtp(string $secret, ?int $timestamp = null, int $period = 30, int $digits = 6): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = (int) floor($timestamp / $period);

        // Pack 64-bit integer into 8 bytes (big-endian)
        $packedTime = pack('N*', 0, $timeSlice);

        $key = $this->base32Decode($secret);
        $hash = hash_hmac('sha1', $packedTime, $key, true);

        // Dynamic truncation (RFC 4226 / RFC 6238)
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % (10 ** $digits);

        return str_pad((string) $otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code against a secret key with a tolerance window.
     */
    public function verifyOtp(string $secret, string $code, int $window = 1, int $period = 30, int $digits = 6, ?int $timestamp = null): bool
    {
        $cleanCode = preg_replace('/[^0-9]/', '', $code);
        if (strlen($cleanCode) !== $digits) {
            return false;
        }

        $timestamp = $timestamp ?? time();

        for ($i = -$window; $i <= $window; $i++) {
            $checkTime = $timestamp + ($i * $period);
            $expected = $this->calculateOtp($secret, $checkTime, $period, $digits);
            if (hash_equals($expected, $cleanCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate standard otpauth URI compatible with Google Authenticator.
     */
    public function getOtpAuthUri(string $label, string $secret, string $issuer = 'Hospital IT System'): string
    {
        $issuerEncoded = rawurlencode($issuer);
        $labelEncoded = rawurlencode($label);
        $secret = strtoupper(trim($secret));

        return "otpauth://totp/{$issuerEncoded}:{$labelEncoded}?secret={$secret}&issuer={$issuerEncoded}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Get QR Code image URL for easy scanning in Google Authenticator.
     */
    public function getQrCodeUrl(string $otpAuthUri): string
    {
        // Use QR server / quickchart for high quality QR rendering
        return 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . rawurlencode($otpAuthUri);
    }
}
