<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    /** @param array<string, mixed> $meta */
    public function record(string $action, ?Model $entity = null, array $meta = [], ?Request $request = null): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entity ? $entity::class : 'system',
            'entity_id' => $entity?->getKey(),
            'meta' => $meta ?: null,
            'ip_address' => ($request ?? request())->ip(),
        ]);
    }
}
