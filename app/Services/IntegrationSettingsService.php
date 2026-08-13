<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class IntegrationSettingsService
{
    /**
     * Integrations that are actively consumed by BoardMatch.
     *
     * Secret fields are never returned to an HTML value attribute. A blank
     * secret submission keeps the current value, while an explicit reset
     * removes only the database override and restores the .env fallback.
     */
    private const GROUPS = [
        [
            'key' => 'ai',
            'title' => 'AI providers',
            'description' => 'Choose the active assistant provider and manage the supported OpenAI-compatible services.',
            'fields' => [
                ['key' => 'ai_provider', 'config' => 'services.ai_evaluation.provider', 'label' => 'Active provider', 'type' => 'select', 'options' => ['groq' => 'Groq', 'openai' => 'OpenAI', 'deepseek' => 'DeepSeek', 'freemodel' => 'FreeModel'], 'rules' => ['nullable', 'in:groq,openai,deepseek,freemodel']],
                ['key' => 'ai_prompt_version', 'config' => 'services.ai_evaluation.prompt_version', 'label' => 'Prompt version', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50']],
                ['key' => 'ai_temperature', 'config' => 'services.ai_evaluation.temperature', 'label' => 'Response temperature', 'type' => 'number', 'step' => '0.1', 'cast' => 'float', 'rules' => ['nullable', 'numeric', 'between:0,2']],

                ['key' => 'groq_enabled', 'config' => 'services.groq.enabled', 'label' => 'Enable Groq', 'type' => 'boolean', 'cast' => 'bool', 'rules' => ['nullable', 'boolean']],
                ['key' => 'groq_api_key', 'config' => 'services.groq.api_key', 'label' => 'Groq API key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'groq_base_url', 'config' => 'services.groq.base_url', 'label' => 'Groq base URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'groq_model', 'config' => 'services.groq.model', 'label' => 'Groq model', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'groq_timeout', 'config' => 'services.groq.timeout', 'label' => 'Groq timeout (seconds)', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,300']],
                ['key' => 'groq_connect_timeout', 'config' => 'services.groq.connect_timeout', 'label' => 'Groq connection timeout', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,120']],

                ['key' => 'openai_api_key', 'config' => 'services.openai.api_key', 'label' => 'OpenAI API key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'openai_base_url', 'config' => 'services.openai.base_url', 'label' => 'OpenAI base URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'openai_model', 'config' => 'services.openai.model', 'label' => 'OpenAI model', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'openai_timeout', 'config' => 'services.openai.timeout', 'label' => 'OpenAI timeout (seconds)', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,300']],
                ['key' => 'openai_connect_timeout', 'config' => 'services.openai.connect_timeout', 'label' => 'OpenAI connection timeout', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,120']],

                ['key' => 'deepseek_enabled', 'config' => 'services.deepseek.enabled', 'label' => 'Enable DeepSeek', 'type' => 'boolean', 'cast' => 'bool', 'rules' => ['nullable', 'boolean']],
                ['key' => 'deepseek_api_key', 'config' => 'services.deepseek.api_key', 'label' => 'DeepSeek API key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'deepseek_base_url', 'config' => 'services.deepseek.base_url', 'label' => 'DeepSeek base URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'deepseek_model', 'config' => 'services.deepseek.model', 'label' => 'DeepSeek model', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'deepseek_timeout', 'config' => 'services.deepseek.timeout', 'label' => 'DeepSeek timeout (seconds)', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,300']],
                ['key' => 'deepseek_connect_timeout', 'config' => 'services.deepseek.connect_timeout', 'label' => 'DeepSeek connection timeout', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,120']],

                ['key' => 'freemodel_enabled', 'config' => 'services.freemodel.enabled', 'label' => 'Enable FreeModel', 'type' => 'boolean', 'cast' => 'bool', 'rules' => ['nullable', 'boolean']],
                ['key' => 'freemodel_api_key', 'config' => 'services.freemodel.api_key', 'label' => 'FreeModel API key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'freemodel_base_url', 'config' => 'services.freemodel.base_url', 'label' => 'FreeModel base URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'freemodel_model', 'config' => 'services.freemodel.model', 'label' => 'FreeModel model', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'freemodel_timeout', 'config' => 'services.freemodel.timeout', 'label' => 'FreeModel timeout (seconds)', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,300']],
                ['key' => 'freemodel_connect_timeout', 'config' => 'services.freemodel.connect_timeout', 'label' => 'FreeModel connection timeout', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,120']],
            ],
        ],
        [
            'key' => 'payments',
            'title' => 'PayMongo',
            'description' => 'Shared Hosted Checkout credentials used when an owner does not have a separate configuration.',
            'fields' => [
                ['key' => 'paymongo_base_url', 'config' => 'services.paymongo.base_url', 'label' => 'API base URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'paymongo_public_key', 'config' => 'services.paymongo.public_key', 'label' => 'Public key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'paymongo_secret_key', 'config' => 'services.paymongo.secret_key', 'label' => 'Secret key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'paymongo_webhook_secret', 'config' => 'services.paymongo.webhook_secret', 'label' => 'Webhook signing secret', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'paymongo_payment_methods', 'config' => 'services.paymongo.payment_methods', 'label' => 'Payment methods', 'type' => 'text', 'cast' => 'csv', 'help' => 'Comma-separated, for example: card, gcash, paymaya, qrph', 'rules' => ['nullable', 'string', 'max:500']],
            ],
        ],
        [
            'key' => 'google',
            'title' => 'Google services',
            'description' => 'Google sign-in, map rendering, and the routing endpoints used by property location features.',
            'fields' => [
                ['key' => 'google_client_id', 'config' => 'services.google.client_id', 'label' => 'OAuth client ID', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'google_client_secret', 'config' => 'services.google.client_secret', 'label' => 'OAuth client secret', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'google_redirect', 'config' => 'services.google.redirect', 'label' => 'OAuth redirect URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'google_maps_api_key', 'config' => 'services.google_maps.api_key', 'label' => 'Google Maps API key', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'google_maps_map_id', 'config' => 'services.google_maps.map_id', 'label' => 'Google Maps map ID', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'google_maps_language', 'config' => 'services.google_maps.language', 'label' => 'Map language', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10']],
                ['key' => 'google_maps_region', 'config' => 'services.google_maps.region', 'label' => 'Map region', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10']],
                ['key' => 'driving_routing_url', 'config' => 'services.google_maps.driving_routing_url', 'label' => 'Driving routing URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
                ['key' => 'walking_routing_url', 'config' => 'services.google_maps.walking_routing_url', 'label' => 'Walking routing URL', 'type' => 'url', 'rules' => ['nullable', 'url:http,https', 'max:2048']],
            ],
        ],
        [
            'key' => 'mail',
            'title' => 'Email delivery',
            'description' => 'SMTP configuration used for verification emails, password resets, and system mail.',
            'fields' => [
                ['key' => 'mail_default', 'config' => 'mail.default', 'label' => 'Default mailer', 'type' => 'select', 'options' => ['smtp' => 'SMTP', 'log' => 'Log only', 'array' => 'Array (testing)'], 'rules' => ['nullable', 'in:smtp,log,array']],
                ['key' => 'mail_scheme', 'config' => 'mail.mailers.smtp.scheme', 'label' => 'SMTP scheme', 'type' => 'select', 'options' => ['smtp' => 'SMTP', 'smtps' => 'SMTPS'], 'rules' => ['nullable', 'in:smtp,smtps']],
                ['key' => 'mail_host', 'config' => 'mail.mailers.smtp.host', 'label' => 'SMTP host', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'mail_port', 'config' => 'mail.mailers.smtp.port', 'label' => 'SMTP port', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,65535']],
                ['key' => 'mail_username', 'config' => 'mail.mailers.smtp.username', 'label' => 'SMTP username', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['key' => 'mail_password', 'config' => 'mail.mailers.smtp.password', 'label' => 'SMTP password', 'type' => 'secret', 'secret' => true, 'rules' => ['nullable', 'string', 'max:2048']],
                ['key' => 'mail_timeout', 'config' => 'mail.mailers.smtp.timeout', 'label' => 'SMTP timeout (seconds)', 'type' => 'number', 'cast' => 'int', 'rules' => ['nullable', 'integer', 'between:1,300']],
                ['key' => 'mail_from_address', 'config' => 'mail.from.address', 'label' => 'From email', 'type' => 'email', 'rules' => ['nullable', 'email:rfc', 'max:255']],
                ['key' => 'mail_from_name', 'config' => 'mail.from.name', 'label' => 'From name', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            ],
        ],
    ];

    public function applyToRuntimeConfig(): void
    {
        try {
            if (! Schema::hasTable('integration_settings')) {
                return;
            }

            $definitions = $this->definitions();

            IntegrationSetting::query()
                ->whereIn('key', array_keys($definitions))
                ->get()
                ->each(function (IntegrationSetting $setting) use ($definitions): void {
                    $definition = $definitions[$setting->key] ?? null;

                    if ($definition === null) {
                        return;
                    }

                    config()->set(
                        $definition['config'],
                        $this->castFromStorage($setting->value, $definition['cast'] ?? 'string')
                    );
                });
        } catch (Throwable) {
            // The application must still boot during first deployment,
            // migrations, recovery, or when encrypted data cannot be read.
        }
    }

    public function groupsForAdmin(): array
    {
        $stored = collect();

        try {
            if (Schema::hasTable('integration_settings')) {
                $stored = IntegrationSetting::query()->get()->keyBy('key');
            }
        } catch (Throwable) {
            $stored = collect();
        }

        return collect(self::GROUPS)->map(function (array $group) use ($stored): array {
            $group['fields'] = collect($group['fields'])->map(function (array $field) use ($stored): array {
                $record = $stored->get($field['key']);
                $resolved = config($field['config']);
                $isSecret = (bool) ($field['secret'] ?? false);

                $field['has_override'] = $record !== null;
                $field['configured'] = $field['type'] === 'boolean'
                    ? (bool) $resolved
                    : filled($resolved);
                $field['source'] = $record !== null ? 'Admin override' : '.env fallback';
                $field['value'] = $isSecret ? '' : $this->valueForInput($resolved, $field['cast'] ?? 'string');

                return $field;
            })->all();

            $group['runtime_status'] = $this->runtimeStatusForGroup($group['key']);

            return $group;
        })->all();
    }

    public function definitions(): array
    {
        return collect(self::GROUPS)
            ->flatMap(fn (array $group) => $group['fields'])
            ->keyBy('key')
            ->all();
    }

    public function validationRules(): array
    {
        return collect($this->definitions())
            ->mapWithKeys(fn (array $field, string $key) => ["settings.$key" => $field['rules'] ?? ['nullable', 'string']])
            ->all();
    }

    public function save(array $values, array $resets = []): void
    {
        $definitions = $this->definitions();

        DB::transaction(function () use ($values, $resets, $definitions): void {
            foreach ($definitions as $key => $definition) {
                if (in_array($key, $resets, true)) {
                    IntegrationSetting::query()->where('key', $key)->delete();

                    continue;
                }

                $value = $values[$key] ?? null;

                if (($definition['secret'] ?? false) && blank($value)) {
                    continue;
                }

                if (! array_key_exists($key, $values)) {
                    continue;
                }

                IntegrationSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $this->normalizeForStorage($value, $definition['cast'] ?? 'string')]
                );
            }
        });
    }

    private function normalizeForStorage(mixed $value, string $cast): string
    {
        return match ($cast) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0',
            'int' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            'csv' => collect(explode(',', (string) $value))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->unique()
                ->implode(','),
            default => trim((string) $value),
        };
    }

    private function castFromStorage(?string $value, string $cast): mixed
    {
        return match ($cast) {
            'bool' => $value === '1',
            'int' => (int) $value,
            'float' => (float) $value,
            'csv' => collect(explode(',', (string) $value))->map(fn (string $item) => trim($item))->filter()->values()->all(),
            default => $value,
        };
    }

    private function valueForInput(mixed $value, string $cast): mixed
    {
        return match ($cast) {
            'bool' => (bool) $value,
            'csv' => is_array($value) ? implode(', ', $value) : (string) $value,
            default => $value,
        };
    }

    private function runtimeStatusForGroup(string $group): array
    {
        if ($group === 'ai') {
            $provider = strtolower(trim((string) config('services.ai_evaluation.provider', 'openai')));
            $provider = in_array($provider, ['groq', 'openai', 'deepseek', 'freemodel'], true)
                ? $provider
                : 'openai';
            $enabled = $provider === 'openai' || (bool) config("services.$provider.enabled");
            $active = $enabled && filled(config("services.$provider.api_key"));
            $providerLabel = match ($provider) {
                'groq' => 'Groq',
                'deepseek' => 'DeepSeek',
                'freemodel' => 'FreeModel',
                default => 'OpenAI',
            };

            return [
                'active' => $active,
                'label' => $active ? "$providerLabel in use" : "$providerLabel not configured",
                'detail' => $active
                    ? "$providerLabel is the active system AI provider."
                    : "$providerLabel is selected but cannot be used until it is enabled and has an API key.",
            ];
        }

        if ($group === 'payments') {
            $active = filled(config('services.paymongo.public_key'))
                && filled(config('services.paymongo.secret_key'));

            return [
                'active' => $active,
                'label' => $active ? 'In use by system' : 'Not configured',
                'detail' => $active
                    ? 'Shared PayMongo checkout is available across the payment workflow.'
                    : 'PayMongo needs both a public key and secret key.',
            ];
        }

        if ($group === 'google') {
            $oauthActive = filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret'))
                && filled(config('services.google.redirect'));

            return [
                'active' => $oauthActive,
                'label' => $oauthActive ? 'Google sign-in in use' : 'Sign-in not configured',
                'detail' => $oauthActive
                    ? 'Google OAuth is available on login and registration pages.'
                    : 'Google sign-in needs a client ID, client secret, and redirect URL.',
            ];
        }

        $smtpActive = config('mail.default') === 'smtp'
            && filled(config('mail.mailers.smtp.host'));

        return [
            'active' => $smtpActive,
            'label' => $smtpActive ? 'SMTP in use' : 'SMTP not in use',
            'detail' => $smtpActive
                ? 'SMTP is the active mail delivery method.'
                : 'Choose SMTP as the default mailer to use these credentials.',
        ];
    }
}
