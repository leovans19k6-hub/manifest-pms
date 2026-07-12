<?php

return [
    'activity_retention_days' => (int) env('ACTIVITY_LOG_RETENTION_DAYS', 90),
    'audit_retention_days' => env('AUDIT_LOG_RETENTION_DAYS'),
];
