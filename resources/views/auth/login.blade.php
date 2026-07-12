<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in | Manifest Stay PMS</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<main><h1>Manifest Stay PMS</h1><form method="POST" action="{{ route('login.store') }}">@csrf
<label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
@error('email')<p>{{ $message }}</p>@enderror
<label>Password <input type="password" name="password" required></label>
<label><input type="checkbox" name="remember" value="1"> Remember me</label>
<button type="submit">Sign in</button></form></main>
</body></html>
