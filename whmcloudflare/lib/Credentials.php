<?php

final class Credentials {
    public string $apiToken = '';
    public string $email = '';
    public string $globalApiKey = '';

    public static function fromArray(array $data): self {
        $c = new self();
        $c->apiToken = (string) ($data['api_token'] ?? '');
        $c->email = (string) ($data['email'] ?? '');
        $c->globalApiKey = (string) ($data['global_api_key'] ?? '');
        return $c;
    }

    public static function fromEncryptedConfig(array $data): self {
        $plain = $data;
        foreach (['api_token', 'global_api_key'] as $key) {
            $raw = (string) ($plain[$key] ?? '');
            if ($raw !== '' && strpos($raw, 'enc:') === 0) {
                $plain[$key] = Security::decrypt(substr($raw, 4));
            }
        }
        return self::fromArray($plain);
    }

    public function isConfigured(): bool {
        return $this->apiToken !== '' || ($this->email !== '' && $this->globalApiKey !== '');
    }

    public function toArray(): array {
        return [
            'api_token' => $this->apiToken,
            'email' => $this->email,
            'global_api_key' => $this->globalApiKey,
        ];
    }
}
