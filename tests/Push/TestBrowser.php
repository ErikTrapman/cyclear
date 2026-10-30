<?php declare(strict_types=1);

namespace App\Tests\Push;

/**
 * The browser's side of a push subscription: the keys it gives the server, and decrypting what the push service
 * delivers (RFC 8291, aes128gcm).
 */
final class TestBrowser
{
    /** DER prefix of a P-256 public key; the raw point follows */
    private const string P256_PUBLIC_KEY_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    public readonly string $p256dh;

    public readonly string $auth;

    private \OpenSSLAsymmetricKey $privateKey;

    private string $publicKey;

    private string $authSecret;

    public function __construct(public readonly string $endpoint)
    {
        $this->privateKey = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $ec = openssl_pkey_get_details($this->privateKey)['ec'];
        $this->publicKey = "\x04" . str_pad($ec['x'], 32, "\0", STR_PAD_LEFT) . str_pad($ec['y'], 32, "\0", STR_PAD_LEFT);
        $this->authSecret = random_bytes(16);
        $this->p256dh = self::base64url($this->publicKey);
        $this->auth = self::base64url($this->authSecret);
    }

    /**
     * @return array<string, mixed> the notification as public/sw.js reads it
     */
    public function decrypt(string $body): array
    {
        // Header: salt (16), record size (4), key id length (1), key id (the server's public key)
        $salt = substr($body, 0, 16);
        $serverKeyLength = ord($body[20]);
        $serverKey = substr($body, 21, $serverKeyLength);
        $ciphertext = substr($body, 21 + $serverKeyLength);

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode(hex2bin(self::P256_PUBLIC_KEY_PREFIX) . $serverKey), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $secret = openssl_pkey_derive($pem, $this->privateKey);
        $ikm = hash_hmac('sha256', "WebPush: info\0" . $this->publicKey . $serverKey . "\1", hash_hmac('sha256', $secret, $this->authSecret, true), true);
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $key = substr(hash_hmac('sha256', "Content-Encoding: aes128gcm\0\1", $prk, true), 0, 16);
        $nonce = substr(hash_hmac('sha256', "Content-Encoding: nonce\0\1", $prk, true), 0, 12);
        $plaintext = openssl_decrypt(substr($ciphertext, 0, -16), 'aes-128-gcm', $key, OPENSSL_RAW_DATA, $nonce, substr($ciphertext, -16));

        // The last record ends with a 0x02 delimiter, then padding
        return json_decode(substr(rtrim($plaintext, "\0"), 0, -1), true, 512, JSON_THROW_ON_ERROR);
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
