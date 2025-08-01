<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Page Expired</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5ee9dff 0%, #b96565ff 100%);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
        }
        .error-title {
            font-size: 24px;
            color: #2c3e50;
            margin: 20px 0;
        }
        .error-message {
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #95a5a6;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">419</h1>
        <h2 class="error-title">Halaman Kedaluwarsa</h2>
        <p class="error-message">
            Sesi Anda telah berakhir atau token keamanan tidak valid. 
            Ini biasanya terjadi ketika Anda meninggalkan halaman terlalu lama 
            atau mencoba mengirimkan form yang sudah tidak valid.
        </p>
        <div>
            <a href="{{ url()->previous() }}" class="btn">Kembali ke Halaman Sebelumnya</a>
            <a href="{{ route('login') }}" class="btn btn-secondary">Login Ulang</a>
        </div>
    </div>
</body>
</html> 