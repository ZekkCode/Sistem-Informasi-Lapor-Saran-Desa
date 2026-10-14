<?php

namespace Tests\Concerns;

/**
 * Menyediakan helper untuk field anti-bot yang diperlukan form publik.
 */
trait WithAntiBotFields
{
    /**
     * Menambahkan field anti-bot yang valid ke payload form.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function withAntiBotFields(array $payload): array
    {
        return array_merge([
            '_website' => '',
            '_form_rendered_at' => (string) (time() - 10),
        ], $payload);
    }
}
