<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CoolClean Dashboard</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            color: #17324d;
            background: #eef9fd;
            font-family: Arial, sans-serif;
        }
        main {
            width: min(560px, calc(100% - 48px));
            padding: 36px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 55px rgba(23, 50, 77, .12);
        }
        h1 { color: #087ca7; }
        button {
            padding: 10px 18px;
            border: 0;
            border-radius: 8px;
            color: white;
            background: #087ca7;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main>
        <h1>Login successful</h1>

        {{-- auth()->user() returns the user stored in the login session. --}}
        <p>Welcome, {{ auth()->user()->FULL_NAME }}.</p>
        <p>Your user ID is {{ auth()->user()->ID }}.</p>

        {{-- Logout changes the session, so it must use POST and a CSRF token. --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
