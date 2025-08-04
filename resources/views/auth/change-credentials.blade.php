@extends('layouts.app')

@section('content')
<div class="inner-page">
    <div class="card">
        <h2><div class="card-header">Ganti Username dan Password</div></h2>
        <div class="card-body">
            <form id="credentialsForm">
                @csrf
                <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                          <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">@</span>
                            <input type="text" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="username" id="username" required>
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">*</span>
                            <input type="password" class="form-control" placeholder="password baru anda" aria-label="pssword" aria-describedby="basic-addon1"  name="password" id="password" required>
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">*</span>
                            <input type="password" class="form-control" placeholder="konfirmasi password" aria-label="pssword" aria-describedby="basic-addon1"  name="password_confirmation" id="password_confirmation" required>
                          </div>
                        </div>
                
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Tambahkan link Toastr di layout master atau di sini -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
document.getElementById('credentialsForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    fetch("{{ route('change.credentials.update') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: data
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP error ' + res.status);
        return res.json();
    })
    .then(data => {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

        if (data.errors) {
            Object.entries(data.errors).forEach(([key, value]) => {
                const input = document.getElementById(key);
                const errorBox = document.getElementById(`${key}-error`);

                if (input) input.classList.add('is-invalid');
                if (errorBox) errorBox.textContent = value[0];

                // ✅ Toastr error global
                if (key === 'password' || key === 'password_confirmation') {
                    toastr.error(value[0]); // Tampilkan toastr error
                }
            });
        } else {
            toastr.success(data.success);
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        }
    })
    .catch(error => {
        console.error(error);
        toastr.error('Terjadi kesalahan pada server\n periksa password.');
    });
});
</script>

@endsection