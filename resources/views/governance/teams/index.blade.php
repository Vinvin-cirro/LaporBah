@extends('layouts.governance')
@section('title', 'Manajemen Tim')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h4 class="fw-bold mb-1">Manajemen Tim</h4><p class="text-muted mb-0">Buat tim lapangan dan atur beberapa Officer dalam satu tim.</p></div>
        <a href="{{ route('governance.teams.create') }}" class="btn text-white" style="background:#020d32"><i class="bi bi-plus-lg me-1"></i> Tambah Tim</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="row g-3">
        @forelse($teams as $team)
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2"><div><h5 class="fw-bold mb-1">{{ $team->name }}</h5><div class="small text-muted">{{ $team->institution_name ?: 'Instansi belum ditentukan' }}</div></div><span class="badge {{ $team->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $team->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                @if($team->description)<p class="text-muted small mb-3">{{ $team->description }}</p>@endif
                <div class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-people fs-5" style="color:#020d32"></i><strong>{{ $team->members_count }} Officer</strong><span class="text-muted small">· {{ $team->active_assignments_count }} tugas aktif</span></div>
                <div class="d-flex flex-wrap gap-2 mb-3">@foreach($team->members->take(5) as $member)<span class="badge rounded-pill text-bg-light border text-dark">{{ $member->name }}</span>@endforeach @if($team->members_count > 5)<span class="badge rounded-pill text-bg-light border text-dark">+{{ $team->members_count - 5 }}</span>@endif</div>
                <div class="d-flex gap-2 mt-auto"><a href="{{ route('governance.teams.edit', $team) }}" class="btn btn-sm btn-outline-dark flex-fill">Kelola Tim</a><form method="POST" action="{{ route('governance.teams.toggle', $team) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $team->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" onclick="return confirm('Ubah status tim ini?')">{{ $team->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div>
            </div></div>
        </div>
        @empty
        <div class="col-12"><div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center py-5"><i class="bi bi-people fs-1 text-muted"></i><h5 class="fw-bold mt-3">Belum ada tim</h5><p class="text-muted">Buat tim pertama untuk mulai mengatur penugasan Officer.</p><a href="{{ route('governance.teams.create') }}" class="btn text-white" style="background:#020d32">Tambah Tim</a></div></div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $teams->links() }}</div>
</div>
@endsection
