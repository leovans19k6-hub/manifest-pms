<?php

return [
    'login_attempts' => (int) env('AUTH_LOGIN_ATTEMPTS', 5),
    'session_secure_cookie' => (bool) env('SESSION_SECURE_COOKIE', false),
    'session_same_site' => env('SESSION_SAME_SITE', 'lax'),
];
