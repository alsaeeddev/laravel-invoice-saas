<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Your Business — Invoice SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
        .brand-icon { width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1, #0ea5e9); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; margin: 0 auto 1rem; }
        .form-control { border-radius: 10px; padding: 0.65rem 1rem; }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; border-radius: 10px; padding: 0.7rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="container" style="max-width:460px;">
    <div class="card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon">🏢</div>
            <h4 class="fw-800">Set up your business</h4>
            <p class="text-muted" style="font-size:0.9rem;">Enter your business name to get started.</p>
        </div>
        <form method="POST" action="{{ route('business.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-600">Business Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Acme Corp" autofocus required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">Create Business</button>
        </form>
    </div>
</div>
</body>
</html>
