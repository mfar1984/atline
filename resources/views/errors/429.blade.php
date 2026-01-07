<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .container {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 450px;
            margin: 1rem;
        }
        
        .icon {
            font-size: 80px;
            color: #ef4444;
            margin-bottom: 1rem;
        }
        
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .error-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .countdown {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            color: #dc2626;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }
        
        .countdown-icon {
            font-size: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-icon {
            font-size: 18px;
        }
        
        .warning-box {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 0.5rem;
            text-align: left;
        }
        
        .warning-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.5rem;
        }
        
        .warning-text {
            font-size: 0.7rem;
            color: #a16207;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="material-symbols-outlined icon">block</span>
        <div class="error-code">429</div>
        <h1 class="error-title">Too Many Requests</h1>
        <p class="error-message">
            {{ $message ?? 'Anda telah membuat terlalu banyak permintaan. Sila tunggu sebentar sebelum mencuba lagi.' }}
        </p>
        
        <div class="countdown">
            <span class="material-symbols-outlined countdown-icon">timer</span>
            <span>Sila tunggu <strong id="countdown">{{ $retryAfter ?? 60 }}</strong> saat</span>
        </div>
        
        <br><br>
        
        <a href="{{ route('login') }}" class="btn" id="retry-btn" style="pointer-events: none; opacity: 0.5;">
            <span class="material-symbols-outlined btn-icon">refresh</span>
            Cuba Lagi
        </a>
        
        <div class="warning-box">
            <div class="warning-title">
                <span class="material-symbols-outlined" style="font-size: 16px;">warning</span>
                Amaran Keselamatan
            </div>
            <p class="warning-text">
                IP anda telah direkodkan. Cubaan berulang yang mencurigakan akan dilaporkan kepada pentadbir sistem. 
                Jika anda adalah pengguna sah, sila hubungi pentadbir.
            </p>
        </div>
    </div>
    
    <script>
        let seconds = {{ $retryAfter ?? 60 }};
        const countdownEl = document.getElementById('countdown');
        const retryBtn = document.getElementById('retry-btn');
        
        const timer = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                retryBtn.style.pointerEvents = 'auto';
                retryBtn.style.opacity = '1';
                countdownEl.parentElement.innerHTML = '<span class="material-symbols-outlined countdown-icon">check_circle</span> Anda boleh cuba lagi sekarang';
                countdownEl.parentElement.style.background = '#f0fdf4';
                countdownEl.parentElement.style.borderColor = '#86efac';
                countdownEl.parentElement.style.color = '#16a34a';
            }
        }, 1000);
    </script>
</body>
</html>
