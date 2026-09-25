<?php

/*
| Deteksi otomatis penyimpanan objek (Cloudflare R2 / S3). Begitu AWS_BUCKET
| terisi, disk utama pindah ke "s3" dan unggah langsung aktif tanpa flag
| tambahan. Di Vercel, unggah langsung diwajibkan karena runtime PHP-nya
| tanpa GD. Semua nilai tetap bisa ditimpa lewat variable environment.
*/
$objectStorageReady = filled(env('AWS_BUCKET'));
$onVercel = filled(env('VERCEL'));

return [
    /*
    | Disk utama dapat diarahkan ke "public" untuk lokal atau "s3" untuk R2.
    | Google Drive di bawah ini selalu diperlakukan sebagai salinan cadangan.
    */
    'disk' => env('REPORT_MEDIA_DISK', $objectStorageReady ? 's3' : 'public'),

    'direct_upload' => [
        'enabled' => (bool) env('REPORT_DIRECT_UPLOAD', $objectStorageReady),
        'required' => (bool) env('REPORT_DIRECT_UPLOAD_REQUIRED', $objectStorageReady && $onVercel),
        'disk' => env('REPORT_DIRECT_UPLOAD_DISK', 's3'),
        'expires_after_minutes' => (int) env('REPORT_DIRECT_UPLOAD_EXPIRES', 15),
        'max_files' => 5,
        'max_file_size' => 5 * 1024 * 1024,
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
    ],

    'google_drive' => [
        'enabled' => (bool) env('GOOGLE_DRIVE_BACKUP_ENABLED', false),
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'queue' => env('GOOGLE_DRIVE_BACKUP_QUEUE', 'backups'),
        'token_url' => 'https://oauth2.googleapis.com/token',
        'api_url' => 'https://www.googleapis.com/drive/v3',
        'upload_url' => 'https://www.googleapis.com/upload/drive/v3',
    ],
];
