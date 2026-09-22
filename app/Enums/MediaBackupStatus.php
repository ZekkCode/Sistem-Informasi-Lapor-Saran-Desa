<?php

namespace App\Enums;

enum MediaBackupStatus: string
{
    case Pending = 'pending';
    case Syncing = 'syncing';
    case Synced = 'synced';
    case Failed = 'failed';
}
