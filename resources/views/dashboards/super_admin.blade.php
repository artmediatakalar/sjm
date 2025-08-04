@extends('layouts.app')

@section('content')

          <div class="page-inner">
@auth
<script>
    window.userId = {{ auth()->id() }};
</script>
@endauth
            <div class="row">

            <div class="row">
              <div class="col-md-4">
                <div class="card card-secondary bg-secondary-gradient">
                  <div class="card-body bubble-shadow">
                    <h1>10M</h1>
                    <h5 class="op-8">Reward</h5>
                    <div class="pull-right">
                      <h3 class="fw-bold op-8"> XX<sup> Point</sup></h3>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card card-secondary bg-secondary-gradient">
                  <div class="card-body bubble-shadow">
                    <h1>Rp. {{ number_format($totalBonus, 0, ',', '.') }}</h1>
                    <h5 class="op-8">Bonus Pasangan</h5>
                    <div class="pull-right">
                      <h3 class="fw-bold op-8"> {{$user->pairing_point}}<sup> Point</sup></h3>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card card-secondary bg-secondary-gradient">
                  <div class="card-body curves-shadow">
                    <h1>{{$user->pairing_count}}</h1>
                    <h5 class="op-8">Jumlah Pasangan</h5>
                    <div class="pull-right">
                      <h3 class="fw-bold op-8"> {{$user->pairing_count}}<sup> L/R</sup></p></h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>

              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small"
                        >
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Pengunjung</p>
                          <h4 class="card-title">1,294</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-info bubble-shadow-small"
                        >
                          <i class="fas fa-user-check"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Member</p>
                          <h4 class="card-title" id="memberCount">0</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-success bubble-shadow-small"
                        >
                          <i class="fas fa-luggage-cart"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Penjualan</p>
                          <h4 class="card-title">$ 1,345</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-secondary bubble-shadow-small"
                        >
                          <i class="far fa-check-circle"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Pesanan</p>
                          <h4 class="card-title">576</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>


          </div>
          
@push('scripts')

<!-- Coba taruh script ini sementara di luar push -->
<<script>

 document.addEventListener('DOMContentLoaded', function () {
 console.log('toastr:', typeof toastr);
        console.log("✅ DOM ready");

        // Ambil data awal member
        fetch('/api/member-count')
            .then(res => res.json())
            .then(data => {
                console.log('📦 Initial member count:', data.count);
                const el = document.getElementById('memberCount');
                if (el) el.innerText = data.count;
            })
            .catch(err => console.error('❌ Gagal ambil jumlah member awal:', err));

        // Echo koneksi
        if (window.Echo?.connector?.pusher?.connection) {
            window.Echo.connector.pusher.connection.bind('connected', function() {
                console.log('🔗 Echo connected to Pusher!');
            });
        }

        // Tunggu Echo siap
        let tries = 10;
        function setupEchoListener() {
            if (typeof window.Echo === 'undefined' || typeof window.Echo.channel !== 'function') {
                console.warn('⌛ Echo belum siap, coba lagi...');
                if (--tries > 0) return setTimeout(setupEchoListener, 500);
                console.error('❌ Laravel Echo gagal disiapkan.');
                return;
            }

            console.log("✅ Echo SIAP, listening...");
            window.Echo.channel('members')
                .listen('.MemberCountUpdated', (e) => {
                    console.log('📡 Broadcast diterima:', e);
                    const el = document.getElementById('memberCount');
                    if (el) el.innerText = e.count;
                });
        }

        setupEchoListener();
    });
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>



@endpush
@endsection
