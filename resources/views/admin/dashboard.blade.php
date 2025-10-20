@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Dasbor</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
          <li class="breadcrumb-item active" aria-current="page">Dasbor</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
              <div>
                <h4 class="card-title mb-1">Detail Kunjungan Landing Page</h4>
                <p class="text-muted mb-0">Pantau performa landing page berdasarkan rentang tanggal yang dipilih.</p>
              </div>
              <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-items-sm-end">
                <div class="form-group mb-2 mb-sm-0 mr-sm-3">
                  <label for="from_date" class="small text-muted">Dari</label>
                  <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $fromDate->toDateString() }}" />
                </div>
                <div class="form-group mb-2 mb-sm-0 mr-sm-3">
                  <label for="to_date" class="small text-muted">Sampai</label>
                  <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $toDate->toDateString() }}" />
                </div>
                <input type="hidden" name="page" value="{{ $selectedPage }}" />
                <button type="submit" class="btn btn-primary">Terapkan</button>
              </form>
            </div>

            @php
              $queryBase = [
                  'from_date' => $fromDate->toDateString(),
                  'to_date' => $toDate->toDateString(),
              ];
              $pageTabs = ['all' => 'All Pages'];
              foreach ($availablePages as $pageItem) {
                  $pageTabs[$pageItem] = $pageItem;
              }
            @endphp

            <ul class="nav nav-tabs" role="tablist">
              @foreach($pageTabs as $pageKey => $label)
                @php
                  $isActive = $selectedPage === $pageKey;
                  $tabQuery = array_merge($queryBase, ['page' => $pageKey]);
                @endphp
                <li class="nav-item" role="presentation">
                  <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ route('admin.dashboard', $tabQuery) }}">
                    {{ $label ?: 'Halaman Tanpa Nama' }}
                  </a>
                </li>
              @endforeach
            </ul>

            <div class="mt-4">
              <div class="chart-container" style="height: 360px;">
                <canvas id="landingPageVisitsChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  (function() {
    var canvas = document.getElementById('landingPageVisitsChart');
    if (!canvas || typeof Chart === 'undefined') {
      return;
    }

    var ctx = canvas.getContext('2d');
    var chartPayload = @json($chartData);

    var datasets = [
      {
        label: 'Total',
        data: chartPayload.series.total,
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.1)',
        borderWidth: 2,
        fill: false,
        lineTension: 0.2,
        pointRadius: 3,
      },
      {
        label: 'Unik',
        data: chartPayload.series.unique,
        borderColor: '#059669',
        backgroundColor: 'rgba(5, 150, 105, 0.1)',
        borderWidth: 2,
        fill: false,
        lineTension: 0.2,
        pointRadius: 3,
      },
      {
        label: 'Primer',
        data: chartPayload.series.primary,
        borderColor: '#ea580c',
        backgroundColor: 'rgba(234, 88, 12, 0.1)',
        borderWidth: 2,
        fill: false,
        lineTension: 0.2,
        pointRadius: 3,
      },
      {
        label: 'Sekunder',
        data: chartPayload.series.secondary,
        borderColor: '#9333ea',
        backgroundColor: 'rgba(147, 51, 234, 0.1)',
        borderWidth: 2,
        fill: false,
        lineTension: 0.2,
        pointRadius: 3,
      }
    ];

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartPayload.labels,
        datasets: datasets
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: true
        },
        tooltips: {
          mode: 'index',
          intersect: false
        },
        hover: {
          mode: 'nearest',
          intersect: true
        },
        scales: {
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            }
          }],
          yAxes: [{
            display: true,
            ticks: {
              beginAtZero: true,
              precision: 0
            },
            scaleLabel: {
              display: true,
              labelString: 'Jumlah Kunjungan'
            }
          }]
        }
      }
    });
  })();
</script>
@endsection
