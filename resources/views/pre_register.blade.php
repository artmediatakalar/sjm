@extends('layouts.app')

@section('content')
<div class="page-inner">
    <h3 class="mb-4">Daftar Pra-Registrasi (Menunggu Verifikasi)</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No. WA</th>
                    <th>Kode Sponsor</th>
                    <th>Metode</th>
                    <th>Bukti Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pending as $item)
                    <tr class="text-center">
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ $item->sponsor_id }}</td>
                        <td>
                            @if($item->payment_method == 'rekening')
                                <span class="badge bg-primary">Rekening</span>
                            @elseif($item->payment_method == 'qris')
                                <span class="badge bg-warning text-dark">QRIS</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->payment_proof)
                                <img src="{{ asset('storage/' . $item->payment_proof) }}"
                                     alt="Bukti"
                                     width="140"
                                     class="img-thumbnail"
                                     onclick="showPreview('{{ asset('storage/' . $item->payment_proof) }}')"
                                     style="cursor:pointer">
                            @else
                                <span class="text-muted">Tidak tersedia</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm btn-approve"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}">
                                ✅ Setujui
                            </button>

                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada pendaftaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Preview Modal --}}
<div id="imagePreview" class="full-preview-overlay" onclick="this.classList.remove('show')">
    <img src="" id="fullPreviewImage" alt="Preview">
</div>

{{-- Style --}}
<style>
    .badge {
        font-size: 0.8rem;
        padding: 0.45em 0.75em;
        border-radius: 0.4rem;
    }
    .full-preview-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(0, 0, 0, 0.85);
        justify-content: center;
        align-items: center;
    }
    .full-preview-overlay img {
        max-width: 90%;
        max-height: 90%;
        border: 4px solid #f5c542;
        border-radius: 12px;
    }
    .full-preview-overlay.show {
        display: flex;
    }
</style>

{{-- Script --}}
<script>
    function showPreview(src) {
        const preview = document.getElementById('imagePreview');
        const img = document.getElementById('fullPreviewImage');
        img.src = src;
        preview.classList.add('show');
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

document.querySelectorAll('.btn-approve').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const name = button.dataset.name;

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch(`/pre-registrations/${id}/approve`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = button.closest('tr');
                row.remove(); // ✅ Hapus baris langsung
                toastr.options = {
                "positionClass": "toast-top-left" // ganti jadi 'toast-top-center', 'toast-top-full-width', dst
                };
                toastr.success('Berhasil Ditambahkan', 'acc member');
                
            } else {
                Swal.fire('❌ Gagal', data.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(err => {
            Swal.fire('❌ Error', 'Terjadi kesalahan saat menghubungi server.', 'error');
            console.error(err);
        });
    });
});

</script>



@endsection
