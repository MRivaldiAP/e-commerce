@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Dasbor</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasbor</a></li>
          <li class="breadcrumb-item active" aria-current="page">Statistik Kunjungan</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title mb-2">Kunjungan Hari Ini</h4>
            <p class="text-muted mb-4">{{ $today->translatedFormat('d F Y') }}</p>
            <div class="d-flex flex-column gap-2">
              <div class="d-flex justify-content-between">
                <span>Total</span>
                <span class="font-weight-bold">{{ $dailyTotals['total_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Unik</span>
                <span class="font-weight-bold">{{ $dailyTotals['unique_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Primer</span>
                <span class="font-weight-bold">{{ $dailyTotals['primary_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Sekunder</span>
                <span class="font-weight-bold">{{ $dailyTotals['secondary_visits'] }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title mb-2">Kunjungan Bulan Ini</h4>
            <p class="text-muted mb-4">{{ $startOfMonth->translatedFormat('d F Y') }} - {{ $endOfMonth->translatedFormat('d F Y') }}</p>
            <div class="d-flex flex-column gap-2">
              <div class="d-flex justify-content-between">
                <span>Total</span>
                <span class="font-weight-bold">{{ $monthlyTotals['total_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Unik</span>
                <span class="font-weight-bold">{{ $monthlyTotals['unique_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Primer</span>
                <span class="font-weight-bold">{{ $monthlyTotals['primary_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Sekunder</span>
                <span class="font-weight-bold">{{ $monthlyTotals['secondary_visits'] }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title mb-2">Kunjungan Tahun Ini</h4>
            <p class="text-muted mb-4">{{ $startOfYear->translatedFormat('d F Y') }} - {{ $endOfYear->translatedFormat('d F Y') }}</p>
            <div class="d-flex flex-column gap-2">
              <div class="d-flex justify-content-between">
                <span>Total</span>
                <span class="font-weight-bold">{{ $yearlyTotals['total_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Unik</span>
                <span class="font-weight-bold">{{ $yearlyTotals['unique_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Primer</span>
                <span class="font-weight-bold">{{ $yearlyTotals['primary_visits'] }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Sekunder</span>
                <span class="font-weight-bold">{{ $yearlyTotals['secondary_visits'] }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Detail Kunjungan Landing Page</h4>
            <ul class="nav nav-tabs" id="visitTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="daily-tab" data-toggle="tab" href="#daily" role="tab" aria-controls="daily" aria-selected="true">Harian</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="monthly-tab" data-toggle="tab" href="#monthly" role="tab" aria-controls="monthly" aria-selected="false">Bulanan</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="yearly-tab" data-toggle="tab" href="#yearly" role="tab" aria-controls="yearly" aria-selected="false">Tahunan</a>
              </li>
            </ul>
            <div class="tab-content" id="visitTabContent">
              <div class="tab-pane fade show active pt-3" id="daily" role="tabpanel" aria-labelledby="daily-tab">
                @include('admin.dashboard.partials.visit-table', ['visits' => $dailyVisits, 'emptyMessage' => 'Belum ada kunjungan hari ini.'])
              </div>
              <div class="tab-pane fade pt-3" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
                @include('admin.dashboard.partials.visit-table', ['visits' => $monthlyVisits, 'emptyMessage' => 'Belum ada kunjungan bulan ini.'])
              </div>
              <div class="tab-pane fade pt-3" id="yearly" role="tabpanel" aria-labelledby="yearly-tab">
                @include('admin.dashboard.partials.visit-table', ['visits' => $yearlyVisits, 'emptyMessage' => 'Belum ada kunjungan tahun ini.'])
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
