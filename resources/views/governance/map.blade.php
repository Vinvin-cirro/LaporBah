
@extends('layouts.governance')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Peta Monitoring</h3>
        <p class="text-muted mb-0">
            Jelajahi sebaran laporan dan kepadatan laporan wilayah.
        </p>
    </div>

    {{-- FILTER --}}
    <form action="{{ route('governance.map') }}"
          method="GET"
          class="row g-2 mb-3 align-items-center">

        <div class="col-12 col-xl-3">
            <input type="search"
                   name="search"
                   class="form-control"
                   value="{{ $search }}"
                   placeholder="Cari lokasi / kode laporan">
        </div>

        <div class="col-6 col-xl-2">
            <select name="category" class="form-select">
                <option value="">Semua kategori</option>
                @foreach (\App\Models\Report::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected($selectedCategory === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-xl-2">
            <select name="period" class="form-select">
                <option value="7" @selected($selectedPeriod === '7')>
                    7 hari terakhir
                </option>
                <option value="30" @selected($selectedPeriod === '30')>
                    30 hari terakhir
                </option>
                <option value="90" @selected($selectedPeriod === '90')>
                    90 hari terakhir
                </option>
                <option value="all" @selected($selectedPeriod === 'all')>
                    Semua waktu
                </option>
            </select>
        </div>

        <div class="col-6 col-xl-2">
            <select name="risk" class="form-select">
                <option value="">Semua tingkat</option>
                <option value="high" @selected($selectedRisk === 'high')>
                    Tinggi
                </option>
                <option value="medium" @selected($selectedRisk === 'medium')>
                    Sedang
                </option>
                <option value="low" @selected($selectedRisk === 'low')>
                    Rendah
                </option>
                <option value="noise" @selected($selectedRisk === 'noise')>
                    Data kurang / noise
                </option>
            </select>
        </div>

        <div class="col-6 col-xl-2">
            <select name="status" class="form-select">
                <option value="">Semua status</option>

                @foreach ([
                    'pending' => 'Pending',
                    'verified' => 'Verified',
                    'investigating' => 'Investigating',
                    'resolved' => 'Resolved',
                    'rejected' => 'Rejected',
                ] as $value => $label)

                    <option value="{{ $value }}"
                            @selected($selectedStatus === $value)>
                        {{ $label }}
                    </option>

                @endforeach
            </select>
        </div>

        <div class="col-12 col-xl-1 d-flex gap-2">

            <button type="submit"
                    class="btn btn-primary flex-grow-1"
                    title="Filter">
                <i class="bi bi-funnel"></i>
            </button>

            <a href="{{ route('governance.map') }}"
               class="btn btn-outline-secondary"
               title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>

        </div>
    </form>


    {{-- MAP AND SUMMARY --}}
    <div class="row g-3 mb-3 align-items-stretch">

        {{-- MAP --}}
        <div class="col-12 col-xl-8">

            <div class="card border rounded-4 h-100">

                <div class="card-body d-flex flex-column">

                    {{-- MAP HEADER --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">

                        <div>
                            <h5 class="fw-bold mb-1">
                                Sebaran laporan
                            </h5>

                            <div class="small text-muted">
                                {{ $mapReports->count() }} laporan sesuai filter
                                · {{ count($clusters) }} cluster ditemukan
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 small">
                            <span><span class="badge rounded-pill bg-primary">&nbsp;</span> PJU Rusak</span>
                            <span><span class="badge rounded-pill bg-warning">&nbsp;</span> Area Gelap</span>
                            <span><span class="badge rounded-pill bg-danger">&nbsp;</span> Begal/Curas</span>
                            <span><span class="badge rounded-pill bg-secondary">&nbsp;</span> Lain-lain</span>
                        </div>

                    </div>

                    {{-- MAP --}}
                    <div id="governanceMap"></div>

                    {{-- MAP INFORMATION --}}
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Cluster menunjukkan kepadatan laporan berdasarkan
                        jarak geografis, bukan bukti kriminalitas.
                    </div>

                </div>

            </div>

        </div>


        {{-- SUMMARY --}}
        <div class="col-12 col-xl-4">

            <div class="card border rounded-4 h-100">

                <div class="card-body d-flex flex-column">

                    {{-- SUMMARY HEADER --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h5 class="fw-bold mb-0">
                            Ringkasan Wilayah
                        </h5>

                        <button type="button"
                                id="resetDistrict"
                                class="btn btn-sm btn-outline-secondary d-none">

                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset

                        </button>

                    </div>


                    {{-- DISTRICT NAME --}}
                    <h4 class="fw-bold mb-2" id="districtName">
                        Seluruh Kota Medan
                    </h4>


                    {{-- RISK BADGE --}}
                    <div id="districtRiskBadge"
                         class="rounded-3 px-3 py-2 mb-3 bg-success-subtle text-success">

                        <span class="small fw-semibold">
                            Belum ada cluster tinggi
                        </span>

                    </div>


                    {{-- TOTAL REPORTS --}}
                    <div class="mb-3">

                        <div class="fw-semibold" id="districtTotal">
                            {{ number_format($statistics['total']) }} laporan
                        </div>

                        <div class="small text-muted mt-1"
                             id="districtVerified">

                            {{ number_format($statistics['verified']) }}
                            terverifikasi / dalam penanganan

                        </div>

                    </div>


                    {{-- CATEGORY --}}
                    @foreach (\App\Models\Report::CATEGORIES as $value => $label)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">{{ $label }}</span>
                            <strong id="districtCategory_{{ $value }}">{{ $statistics[$value] }}</strong>
                        </div>
                    @endforeach


                    {{-- STATUS --}}
                    <div class="d-flex justify-content-between py-2 border-bottom">

                        <span class="text-muted">
                            Menunggu
                        </span>

                        <strong id="districtPending">
                            {{ $statistics['pending'] }}
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between py-2 border-bottom">

                        <span class="text-muted">
                            Ditangani
                        </span>

                        <strong id="districtInvestigating">
                            {{ $statistics['investigating'] }}
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between py-2 border-bottom">

                        <span class="text-muted">
                            Selesai
                        </span>

                        <strong id="districtResolved">
                            {{ $statistics['resolved'] }}
                        </strong>

                    </div>


                    {{-- LATEST REPORT --}}
                    <div class="d-flex justify-content-between py-2 border-bottom">

                        <span class="text-muted">
                            Laporan terakhir
                        </span>

                        <strong id="districtLastReport"
                                class="text-end">
                            -
                        </strong>

                    </div>


                    {{-- DENSITY --}}
                    <div class="mt-4">

                        <div class="fw-semibold mb-2">
                            KEPADATAN LAPORAN
                        </div>


                        <div class="d-flex justify-content-between py-1 small">

                            <span class="text-danger">
                                Tinggi
                            </span>

                            <strong id="districtHigh">
                                {{ $statistics['high'] }}
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between py-1 small">

                            <span class="text-warning">
                                Sedang
                            </span>

                            <strong id="districtMedium">
                                {{ $statistics['medium'] }}
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between py-1 small">

                            <span class="text-success">
                                Rendah
                            </span>

                            <strong id="districtLow">
                                {{ $statistics['low'] }}
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between py-1 small">

                            <span class="text-secondary">
                                Noise / kurang data
                            </span>

                            <strong id="districtNoise">
                                {{ $statistics['noise'] }}
                            </strong>

                        </div>

                    </div>


                    {{-- TOP CLUSTER --}}
                    <div class="mt-4">

                        <div class="small text-uppercase fw-semibold text-muted mb-2">
                            Cluster terpadat
                        </div>

                        <div class="fw-semibold" id="districtTopCluster">
                            {{ $topCluster['label'] ?? 'Belum tersedia' }}
                        </div>

                        <div class="small text-muted"
                             id="districtTopClusterDetail">

                            @if ($topCluster)

                                {{ $topCluster['count'] }} laporan

                            @else

                                Belum ditemukan cluster

                            @endif

                        </div>

                    </div>


                    {{-- REPORT BUTTON --}}
                    <div class="mt-auto pt-4">

                        <a href="{{ route('governance.reports.index') }}"
                           id="districtReportsLink"
                           class="btn btn-dark w-100">

                            Lihat laporan sesuai filter
                            <i class="bi bi-arrow-right ms-1"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- LEGEND --}}
    <div class="card border rounded-4">

        <div class="card-body">

            <div class="fw-semibold mb-2">
                Indikasi kepadatan laporan
            </div>

            <div class="d-flex flex-wrap gap-3 small mb-2">

                <span class="text-danger fw-semibold">
                    ● Tinggi (10+ laporan)
                </span>

                <span class="text-warning fw-semibold">
                    ● Sedang (5–9 laporan)
                </span>

                <span class="text-success fw-semibold">
                    ● Rendah (3–4 laporan)
                </span>

                <span class="text-secondary fw-semibold">
                    ● Noise / kurang data (&lt;3)
                </span>

            </div>

            <div class="small text-muted">

                DBSCAN: eps {{ $epsMeters }} meter
                · minPts {{ $minPoints }}
                · Periode
                {{ $selectedPeriod === 'all'
                    ? 'semua waktu'
                    : $selectedPeriod . ' hari terakhir' }}.

                Parameter ini masih perlu diuji menggunakan data aktual.

            </div>

        </div>

    </div>

</div>
@endsection


{{-- ====================================== --}}
{{-- STYLES --}}
{{-- ====================================== --}}

@push('styles')

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>

    #governanceMap {
        width: 100%;
        min-height: 520px;
        flex: 1 1 auto;
        border-radius: 12px;
    }

    .district-label {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: #1e293b !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-shadow:
            1px 1px 2px white,
            -1px -1px 2px white,
            1px -1px 2px white,
            -1px 1px 2px white;
    }

    .district-label::before {
        display: none !important;
    }

</style>

@endpush


{{-- ====================================== --}}
{{-- JAVASCRIPT --}}
{{-- ====================================== --}}

@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

    // ======================================
    // DATA
    // ======================================

    const reports = @json($mapReports);
    const clusters = @json($clusters);


    // ======================================
    // INITIALIZE MAP
    // ======================================

    const map = L.map('governanceMap').setView(
        [3.5952, 98.6722],
        12
    );


    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);


    // ======================================
    // DISTRICT COLORS
    // ======================================

    const districtColors = [
        '#60a5fa',
        '#34d399',
        '#fbbf24',
        '#f87171',
        '#a78bfa',
        '#fb923c',
        '#2dd4bf',
        '#f472b6',
        '#818cf8',
        '#a3e635',
        '#38bdf8',
        '#c084fc',
        '#facc15',
        '#4ade80',
        '#fb7185',
        '#67e8f9',
        '#fdba74',
        '#93c5fd',
        '#86efac',
        '#d8b4fe',
        '#f9a8d4'
    ];


    let selectedDistrictFeature = null;

    const districtLayers = [];


    // ======================================
    // DISTRICT NAME
    // ======================================

    function getDistrictName(feature) {

        const properties = feature.properties || {};

        return properties.name ||
            properties.NAME ||
            properties.NAMOBJ ||
            properties.kecamatan ||
            properties.district ||
            properties.name_3 ||
            properties.WADMKC ||
            properties.NAMKecamatan ||
            properties.NAMKEC ||
            'Kecamatan';

    }


    // ======================================
    // POINT IN POLYGON
    // ======================================

    function pointInRing(point, ring) {

        const x = point[0];
        const y = point[1];

        let inside = false;

        for (
            let i = 0, j = ring.length - 1;
            i < ring.length;
            j = i++
        ) {

            const xi = ring[i][0];
            const yi = ring[i][1];

            const xj = ring[j][0];
            const yj = ring[j][1];

            const intersect =
                ((yi > y) !== (yj > y)) &&
                (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

            if (intersect) {
                inside = !inside;
            }

        }

        return inside;

    }


    function pointInPolygon(point, polygon) {

        if (!polygon || polygon.length === 0) {
            return false;
        }

        // Polygon utama
        if (!pointInRing(point, polygon[0])) {
            return false;
        }

        // Area holes
        for (let i = 1; i < polygon.length; i++) {

            if (pointInRing(point, polygon[i])) {
                return false;
            }

        }

        return true;

    }


    function pointInGeometry(point, geometry) {

        if (!geometry) {
            return false;
        }

        if (geometry.type === 'Polygon') {

            return pointInPolygon(
                point,
                geometry.coordinates
            );

        }

        if (geometry.type === 'MultiPolygon') {

            return geometry.coordinates.some(polygon =>
                pointInPolygon(point, polygon)
            );

        }

        return false;

    }


    function reportInsideDistrict(report, feature) {

        if (
            report.latitude === null ||
            report.longitude === null ||
            !Number.isFinite(Number(report.latitude)) ||
            !Number.isFinite(Number(report.longitude))
        ) {
            return false;
        }

        return pointInGeometry(
            [
                Number(report.longitude),
                Number(report.latitude)
            ],
            feature.geometry
        );

    }


    // ======================================
    // DISTRICT STYLE
    // ======================================

    function getDistrictStyle(feature, index) {

        const isSelected =
            selectedDistrictFeature === feature;

        return {

            color: isSelected
                ? '#0f172a'
                : '#475569',

            weight: isSelected
                ? 3
                : 1.5,

            fillColor:
                districtColors[index % districtColors.length],

            fillOpacity: isSelected
                ? 0.65
                : 0.35

        };

    }


    function updateDistrictStyles() {

        districtLayers.forEach(item => {

            item.layer.setStyle(
                getDistrictStyle(
                    item.feature,
                    item.index
                )
            );

        });

    }


    // ======================================
    // STATUS LABELS
    // ======================================

    const statusLabels = {

        pending: 'Pending',
        verified: 'Verified',
        investigating: 'Investigating',
        resolved: 'Resolved',
        rejected: 'Rejected'

    };


    const statusColors = {

        pending: 'warning',
        verified: 'primary',
        investigating: 'info',
        resolved: 'success',
        rejected: 'danger'

    };


    const riskColors = {

        high: '#dc3545',
        medium: '#ffc107',
        low: '#198754',
        noise: '#6c757d'

    };


    // ======================================
    // ESCAPE HTML
    // ======================================

    function escapeHtml(value) {

        return String(value ?? '').replace(/[&<>"']/g, char => ({

            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'

        }[char]));

    }


    // ======================================
    // LAYER GROUPS
    // ======================================

    const reportMarkerGroup =
        L.layerGroup().addTo(map);

    const clusterLayerGroup =
        L.layerGroup().addTo(map);


    const reportMarkerLayers = [];
    const clusterLayers = [];
    const markers = [];


    // ======================================
    // UPDATE SUMMARY
    // ======================================

    function updateDistrictSummary(feature = null) {

        const filteredReports = feature

            ? reports.filter(report =>
                reportInsideDistrict(report, feature)
            )

            : reports;


        // ======================================
        // STATISTICS
        // ======================================

        const total = filteredReports.length;


        const categoryKeys = ['pju_rusak', 'area_gelap', 'begal_curas', 'lain_lain'];
        const categoryCounts = Object.fromEntries(categoryKeys.map(key => [key, filteredReports.filter(report => report.category === key).length]));


        const pending = filteredReports.filter(
            report => report.status === 'pending'
        ).length;


        const investigating = filteredReports.filter(
            report => report.status === 'investigating'
        ).length;


        const resolved = filteredReports.filter(
            report => report.status === 'resolved'
        ).length;


        const verified = filteredReports.filter(
            report => [
                'verified',
                'investigating',
                'resolved'
            ].includes(report.status)
        ).length;


        const high = filteredReports.filter(
            report => report.risk === 'high'
        ).length;


        const medium = filteredReports.filter(
            report => report.risk === 'medium'
        ).length;


        const low = filteredReports.filter(
            report => report.risk === 'low'
        ).length;


        const noise = filteredReports.filter(
            report => report.risk === 'noise'
        ).length;


        // ======================================
        // TOP CLUSTER
        // ======================================

        const clusterCounts = {};


        filteredReports.forEach(report => {

            if (report.cluster_id !== null) {

                clusterCounts[report.cluster_id] =
                    (clusterCounts[report.cluster_id] || 0) + 1;

            }

        });


        let topCluster = null;


        Object.entries(clusterCounts).forEach(([id, count]) => {

            if (!topCluster || count > topCluster.count) {

                topCluster = {

                    id: Number(id),
                    count: count

                };

            }

        });


        const clusterInfo = topCluster

            ? clusters.find(cluster =>
                cluster.id === topCluster.id
            )

            : null;


        // ======================================
        // UPDATE PANEL
        // ======================================

        document.getElementById('districtName').textContent =

            feature
                ? getDistrictName(feature)
                : 'Seluruh Kota Medan';


        document.getElementById('districtTotal').textContent =

            `${total.toLocaleString('id-ID')} laporan`;


        document.getElementById('districtVerified').textContent =

            `${verified.toLocaleString('id-ID')} terverifikasi / dalam penanganan`;


        categoryKeys.forEach(key => {
            const target = document.getElementById(`districtCategory_${key}`);
            if (target) target.textContent = categoryCounts[key];
        });


        document.getElementById('districtPending').textContent =
            pending;


        document.getElementById('districtInvestigating').textContent =
            investigating;


        document.getElementById('districtResolved').textContent =
            resolved;


        document.getElementById('districtHigh').textContent =
            high;


        document.getElementById('districtMedium').textContent =
            medium;


        document.getElementById('districtLow').textContent =
            low;


        document.getElementById('districtNoise').textContent =
            noise;


        // ======================================
        // LATEST REPORT
        // ======================================

        document.getElementById('districtLastReport').textContent =

            filteredReports.length > 0
                ? filteredReports[0].created_at
                : '-';


        // ======================================
        // RISK BADGE
        // ======================================

        const badge =
            document.getElementById('districtRiskBadge');


        if (high > 0) {

            badge.className =
                'rounded-3 px-3 py-2 mb-3 bg-danger-subtle text-danger';

            badge.innerHTML =
                '<span class="small fw-semibold">Aktivitas laporan tinggi</span>';

        } else if (medium > 0) {

            badge.className =
                'rounded-3 px-3 py-2 mb-3 bg-warning-subtle text-dark';

            badge.innerHTML =
                '<span class="small fw-semibold">Aktivitas laporan sedang</span>';

        } else if (low > 0) {

            badge.className =
                'rounded-3 px-3 py-2 mb-3 bg-success-subtle text-success';

            badge.innerHTML =
                '<span class="small fw-semibold">Aktivitas laporan rendah</span>';

        } else {

            badge.className =
                'rounded-3 px-3 py-2 mb-3 bg-secondary-subtle text-secondary';

            badge.innerHTML =
                '<span class="small fw-semibold">Belum ada cluster laporan</span>';

        }


        // ======================================
        // TOP CLUSTER
        // ======================================

        document.getElementById('districtTopCluster').textContent =

            clusterInfo?.label ?? 'Belum tersedia';


        document.getElementById('districtTopClusterDetail').textContent =

            topCluster

                ? `${topCluster.count} laporan · ${clusterInfo?.risk ?? 'unknown'}`

                : 'Belum ditemukan cluster';


        // ======================================
        // REPORT LINK
        // ======================================

        const reportsLink =
            document.getElementById('districtReportsLink');


        if (feature) {

            reportsLink.href =

                "{{ route('governance.reports.index') }}" +
                '?search=' +
                encodeURIComponent(getDistrictName(feature));


            reportsLink.innerHTML =

                'Lihat laporan wilayah ' +
                '<i class="bi bi-arrow-right ms-1"></i>';

        } else {

            reportsLink.href =
                "{{ route('governance.reports.index') }}";


            reportsLink.innerHTML =

                'Lihat laporan sesuai filter ' +
                '<i class="bi bi-arrow-right ms-1"></i>';

        }


        // ======================================
        // RESET BUTTON
        // ======================================

        document.getElementById('resetDistrict')
            .classList.toggle('d-none', !feature);

    }


    // ======================================
    // UPDATE MAP LAYERS
    // ======================================

    function updateMapLayers() {

        reportMarkerGroup.clearLayers();
        clusterLayerGroup.clearLayers();


        // ======================================
        // REPORT MARKERS
        // ======================================

        reportMarkerLayers.forEach(item => {

            const visible =

                !selectedDistrictFeature ||

                reportInsideDistrict(
                    item.report,
                    selectedDistrictFeature
                );


            if (visible) {

                reportMarkerGroup.addLayer(
                    item.marker
                );

            }

        });


        // ======================================
        // CLUSTER CIRCLES
        // ======================================

        clusterLayers.forEach(item => {

            const visible =

                !selectedDistrictFeature ||

                pointInGeometry(

                    [
                        Number(item.cluster.longitude),
                        Number(item.cluster.latitude)
                    ],

                    selectedDistrictFeature.geometry

                );


            if (visible) {

                clusterLayerGroup.addLayer(
                    item.layer
                );

            }

        });

    }


    // ======================================
    // LOAD GEOJSON DISTRICTS
    // ======================================

    fetch('/maps/medan-districts.geojson')

        .then(response => {

            if (!response.ok) {

                throw new Error(
                    'GeoJSON gagal dimuat'
                );

            }

            return response.json();

        })

        .then(data => {

            L.geoJSON(data, {

                style: function (feature) {

                    const index =
                        data.features.indexOf(feature);

                    return getDistrictStyle(
                        feature,
                        index
                    );

                },


                onEachFeature: function (feature, layer) {

                    const districtName =
                        getDistrictName(feature);


                    // ======================================
                    // DISTRICT LABEL
                    // ======================================

                    layer.bindTooltip(
                        districtName,
                        {

                            permanent: true,
                            direction: 'center',
                            className: 'district-label'

                        }
                    );


                    // ======================================
                    // DISTRICT POPUP
                    // ======================================

                    layer.bindPopup(

                        `<div class="text-center">
                            <strong>${escapeHtml(districtName)}</strong>
                            <hr class="my-2">
                            <span>Wilayah Administrasi Kecamatan</span>
                            <br>
                            <small>Klik polygon untuk melihat statistik wilayah.</small>
                        </div>`

                    );


                    // ======================================
                    // CLICK DISTRICT
                    // ======================================

                    layer.on('click', function () {

                        selectedDistrictFeature =
                            feature;


                        updateDistrictStyles();

                        updateDistrictSummary(
                            feature
                        );

                        updateMapLayers();


                        // Zoom ke kecamatan
                        map.fitBounds(
                            layer.getBounds(),
                            {

                                padding: [30, 30],
                                maxZoom: 14

                            }
                        );

                    });


                    // ======================================
                    // HOVER
                    // ======================================

                    layer.on('mouseover', function () {

                        if (
                            selectedDistrictFeature !== feature
                        ) {

                            layer.setStyle({

                                weight: 3,
                                fillOpacity: 0.55

                            });

                        }

                    });


                    layer.on('mouseout', function () {

                        layer.setStyle(

                            getDistrictStyle(

                                feature,

                                data.features.indexOf(feature)

                            )

                        );

                    });


                    // ======================================
                    // SAVE LAYER
                    // ======================================

                    districtLayers.push({

                        feature: feature,
                        layer: layer,
                        index: data.features.indexOf(feature)

                    });

                }

            }).addTo(map);


            // Perbarui ukuran map setelah GeoJSON dimuat
            setTimeout(function () {

                map.invalidateSize();

            }, 100);

        })

        .catch(error => {

            console.error(
                'Error memuat polygon kecamatan:',
                error
            );

        });


    // ======================================
    // RESET DISTRICT
    // ======================================

    document.getElementById('resetDistrict')
        .addEventListener('click', function () {

            selectedDistrictFeature = null;


            updateDistrictStyles();

            updateDistrictSummary();

            updateMapLayers();


            map.setView(
                [3.5952, 98.6722],
                12
            );

        });


    // ======================================
    // CLUSTER CIRCLES
    // ======================================

    clusters.forEach(cluster => {

        const color =
            riskColors[cluster.risk] || riskColors.low;


        const circle = L.circle(

            [
                cluster.latitude,
                cluster.longitude
            ],

            {

                radius: 300,

                color: color,
                fillColor: color,
                fillOpacity: 0.12,
                weight: 1

            }

        );


        clusterLayers.push({

            cluster: cluster,
            layer: circle

        });

    });


    // ======================================
    // REPORT MARKERS
    // ======================================

    reports.forEach(report => {


        // Validasi koordinat
        if (

            report.latitude === null ||

            report.longitude === null ||

            !Number.isFinite(
                Number(report.latitude)
            ) ||

            !Number.isFinite(
                Number(report.longitude)
            )

        ) {

            return;

        }


        const categoryColors = { pju_rusak: '#0d6efd', area_gelap: '#ffc107', begal_curas: '#dc3545', lain_lain: '#6c757d' };
        const categoryLabels = { pju_rusak: 'PJU Rusak', area_gelap: 'Area Gelap', begal_curas: 'Begal/Curas', lain_lain: 'Lain-lain' };
        const categoryColor = categoryColors[report.category] || '#6c757d';


        const color =

            report.cluster_id !== null

                ? (
                    riskColors[report.risk] ||
                    riskColors.noise
                )

                : (
                    categoryColor
                );


        const marker = L.circleMarker(

            [

                Number(report.latitude),

                Number(report.longitude)

            ],

            {

                radius:
                    report.cluster_id !== null
                        ? 8
                        : 6,

                color: color,

                fillColor:
                    categoryColor,

                fillOpacity: 0.9,

                weight: 2

            }

        );


        // ======================================
        // POPUP
        // ======================================

        const categoryLabel = categoryLabels[report.category] || 'Lain-lain';


        const statusLabel =

            statusLabels[report.status] ??
            report.status;


        const statusColor =

            statusColors[report.status] ??
            'secondary';


        const popupContent = `

            <div style="min-width:220px">

                <div class="mb-2">

                    <strong>
                        ${escapeHtml(categoryLabel)}
                    </strong>

                    <span class="badge bg-${statusColor} ms-1">
                        ${escapeHtml(statusLabel)}
                    </span>

                </div>


                <p class="small mb-1">

                    <strong>Kode:</strong>

                    ${escapeHtml(
                        report.tracking_code ?? '-'
                    )}

                </p>


                <p class="small mb-1">

                    <strong>Lokasi:</strong>

                    ${escapeHtml(
                        report.location_name ??
                        'Tidak dicantumkan'
                    )}

                </p>


                <p class="small mb-1">

                    <strong>Pelapor:</strong>

                    ${escapeHtml(
                        report.reporter ??
                        'Anonim'
                    )}

                </p>


                <p class="small mb-1">

                    <strong>Tanggal:</strong>

                    ${escapeHtml(
                        report.created_at ?? '-'
                    )}

                </p>


                <p class="small mb-1">

                    <strong>Cluster:</strong>

                    ${
                        report.cluster_id === null

                            ? 'Noise / belum cukup data'

                            : `#${report.cluster_id + 1}
                               (${report.cluster_count} laporan)`

                    }

                </p>


                <p class="small mb-0">

                    <strong>Deskripsi:</strong>

                    <br>

                    ${escapeHtml(
                        report.description ?? '-'
                    )}

                </p>

            </div>

        `;


        marker.bindPopup(
            popupContent
        );


        // Simpan marker
        markers.push(marker);


        reportMarkerLayers.push({

            report: report,
            marker: marker

        });

    });


    // ======================================
    // INITIAL MAP LAYERS
    // ======================================

    updateMapLayers();


    // ======================================
    // INITIAL MAP VIEW
    // ======================================

    if (markers.length > 0) {

        map.fitBounds(

            L.featureGroup(markers).getBounds(),

            {

                padding: [30, 30],
                maxZoom: 14

            }

        );

    }


    // ======================================
    // MAP RESIZE
    // ======================================

    setTimeout(function () {

        map.invalidateSize();

    }, 250);


    window.addEventListener('resize', function () {

        map.invalidateSize();

    });


</script>

@endpush
