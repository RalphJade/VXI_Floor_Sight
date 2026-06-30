<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>VXI Floor Sight</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-200 h-screen w-screen overflow-hidden">
    <div id="app" class="h-full w-full" data-auth="{{ auth()->check() ? '1' : '0' }}"></div>
</body>
</html>
