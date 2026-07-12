<?php

return ['disk' => env('PROPERTY_MEDIA_DISK', 'local'), 'asset_max_bytes' => 10 * 1024 * 1024, 'document_max_bytes' => 25 * 1024 * 1024, 'asset_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'application/pdf'], 'document_mimes' => ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']];
