<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Country</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ✅ Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- ✅ Google Fonts: Poppins --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .card-country {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
            animation: fadeBounceIn 0.6s ease forwards;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .card-country:hover {
            transform: translateY(-5px) scale(1.03);
            border-color: #0d6efd;
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.2);
        }

        @keyframes fadeBounceIn {
            0% { opacity: 0; transform: translateY(30px) scale(0.95); }
            60% { transform: translateY(-8px) scale(1.02); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h1 class="text-center mb-5 fs-3 fw-bold">🌍 Select Your Country</h1>

    <div class="d-flex flex-wrap justify-content-center gap-3">
        @foreach ($countries as $country)
            <div class="card text-center card-country" style="width: 12rem;">
                <a href="{{ route('public.select-country', ['country_id' => $country->id]) }}" class="text-decoration-none text-dark p-3 d-block">
                    <img 
                        src="{{ asset('storage/' . $country->image) }}" 
                        class="card-img-top mb-2"
                        alt="{{ $country->name }}"
                        style="height: 80px; object-fit: contain;"
                    >
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1 fw-semibold">{{ $country->name }}</h6>
                        @if (!empty($country->currency->symbol))
                            <small class="text-muted">{{ $country->currency->symbol }}</small>
                        @endif
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

</body>
</html>
