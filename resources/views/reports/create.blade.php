
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - LaporBah!</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >
</head>

<body class="bg-light">


<nav class="navbar navbar-dark" style="background:#020d32">
    <div class="container">
        <a href="{{ auth()->check() ? route('citizen.dashboard') : url('/') }}"
           class="navbar-brand fw-bold d-flex align-items-center text-warning">
            <img
                src="{{ asset('images/laporbah-logo.png') }}"
                alt="LaporBah! Logo"
                height="38"
                class="me-2"
            >
            LaporBah!
        </a>

        <a href="{{ auth()->check() ? route('citizen.dashboard') : url('/') }}"
           class="btn btn-outline-light btn-sm">
            Kembali
        </a>
    </div>
</nav>


<div class="container py-4">

    <div class="mb-4">
        <h2 class="fw-bold">Buat Laporan</h2>
        <p class="text-secondary">
            Laporkan kejadian atau area yang membutuhkan perhatian.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <h6 class="fw-bold">{{ session('success') }}</h6>

            <p class="mb-1">Tracking Code laporan kamu:</p>

            <div class="d-flex align-items-center gap-2">
                <strong class="fs-5">
                    {{ session('tracking_code') }}
                </strong>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-success"
                    onclick="navigator.clipboard.writeText('{{ session('tracking_code') }}')"
                >
                    Salin
                </button>
            </div>

            <small class="d-block mt-2">
                Simpan kode ini untuk memantau laporan.
            </small>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data laporan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('reports.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">Informasi Laporan</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Kategori laporan
                    </label>

                    <select name="category" class="form-select" required>
                        <option value="">Pilih kategori</option>

                        <option value="begal"
                            @selected(old('category') === 'begal')>
                            Begal / CURAS
                        </option>

                        <option value="dark"
                            @selected(old('category') === 'dark')>
                            Area minim penerangan
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Deskripsi kejadian
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="5"
                        maxlength="5000"
                        placeholder="Jelaskan kejadian atau kondisi area yang ingin dilaporkan..."
                        required
                    >{{ old('description') }}</textarea>

                    <small class="text-secondary">
                        Maksimal 5.000 karakter.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nama atau alamat lokasi
                    </label>

                    <input
                        type="text"
                        name="location_name"
                        class="form-control"
                        value="{{ old('location_name') }}"
                        maxlength="255"
                        placeholder="Contoh: Jalan Ahmad Yani, Bekasi"
                    >
                </div>

            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-2">Lokasi Kejadian</h5>

                <p class="text-secondary small">
                    Gunakan GPS atau klik langsung pada peta untuk menentukan
                    koordinat lokasi.
                </p>

                <button
                    type="button"
                    id="getLocation"
                    class="btn btn-outline-primary btn-sm mb-3"
                >
                    Gunakan lokasi saya
                </button>

                <div
                    id="reportMap"
                    class="rounded border"
                    style="height:350px"
                ></div>

                <div class="row mt-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Latitude</label>

                        <input
                            type="number"
                            step="any"
                            name="latitude"
                            id="latitude"
                            class="form-control"
                            value="{{ old('latitude') }}"
                            required
                            readonly
                        >
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="form-label">Longitude</label>

                        <input
                            type="number"
                            step="any"
                            name="longitude"
                            id="longitude"
                            class="form-control"
                            value="{{ old('longitude') }}"
                            required
                            readonly
                        >
                    </div>
                </div>

                <small id="locationMessage" class="text-secondary">
                    Belum ada lokasi dipilih. Klik peta atau gunakan GPS.
                </small>

            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">Waktu Kejadian</h5>

                <label class="form-label">
                    Tanggal dan waktu kejadian
                </label>

                <input
                    type="datetime-local"
                    name="incident_at"
                    class="form-control"
                    value="{{ old('incident_at') }}"
                >

                <small class="text-secondary">
                    Kosongkan jika tidak mengetahui waktu kejadian.
                </small>

            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">Foto Pendukung</h5>

                <input
                    type="file"
                    name="photos[]"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                    id="photos"
                >

                <small class="text-secondary">
                    Maksimal 5 foto. Format JPG, JPEG, PNG, atau WEBP.
                    Maksimal 5 MB per foto.
                </small>

                <div id="photoPreview" class="mt-3"></div>

            </div>
        </div>

        <div class="alert alert-info">
            Jangan sertakan identitas korban, informasi pribadi,
            atau foto yang dapat membahayakan keselamatan orang lain.
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a
                href="{{ auth()->check() ? route('citizen.dashboard') : url('/') }}"
                class="btn btn-outline-secondary"
            >
                Batal
            </a>

            <button type="submit" class="btn btn-primary px-4">
                Kirim Laporan
            </button>
        </div>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('reportMap').setView([-6.2383, 106.9756], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationMessage = document.getElementById('locationMessage');

    function setLocation(lat, lng) {
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }

        locationMessage.textContent =
            'Lokasi dipilih: ' + lat.toFixed(7) + ', ' + lng.toFixed(7);
    }

    map.on('click', function (event) {
        setLocation(event.latlng.lat, event.latlng.lng);
    });

    document.getElementById('getLocation').addEventListener('click', function () {
        if (!navigator.geolocation) {
            locationMessage.textContent =
                'Browser tidak mendukung fitur GPS.';
            return;
        }

        locationMessage.textContent = 'Mengambil lokasi GPS...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                setLocation(lat, lng);
                map.setView([lat, lng], 16);
            },
            function () {
                locationMessage.textContent =
                    'Lokasi tidak dapat diakses. Periksa izin GPS browser.';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000
            }
        );
    });

    // Restore old coordinates after validation error
    @if (old('latitude') !== null && old('longitude') !== null)
        setLocation(
            Number(@json(old('latitude'))),
            Number(@json(old('longitude')))
        );

        map.setView([
            Number(@json(old('latitude'))),
            Number(@json(old('longitude')))
        ], 16);
    @endif

    // Photo preview
    document.getElementById('photos').addEventListener('change', function () {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';

        const files = Array.from(this.files);

        if (files.length > 5) {
            alert('Maksimal 5 foto.');
            this.value = '';
            return;
        }

        files.forEach(file => {
            const item = document.createElement('div');
            item.className = 'small text-secondary mb-1';
            item.textContent = file.name + ' (' +
                (file.size / 1024 / 1024).toFixed(2) + ' MB)';

            preview.appendChild(item);
        });
    });
</script>

</body>
</html>
