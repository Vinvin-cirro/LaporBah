@extends('layouts.governance')
@section('title', $team->exists ? 'Edit Tim' : 'Tambah Tim')
@section('content')
<div class="container-fluid" style="max-width:1000px">
    <div class="mb-4"><a href="{{ route('governance.teams.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Kembali ke Manajemen Tim</a><h4 class="fw-bold mt-2 mb-1">{{ $team->exists ? 'Edit Tim' : 'Buat Tim Baru' }}</h4><p class="text-muted mb-0">Satu Officer boleh tergabung dalam beberapa tim sekaligus.</p></div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $team->exists ? route('governance.teams.update', $team) : route('governance.teams.store') }}" class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
        @csrf @if($team->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Nama Tim <span class="text-danger">*</span></label><input name="name" class="form-control" maxlength="150" required value="{{ old('name', $team->name) }}" placeholder="Contoh: Tim PJU Medan 01"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Instansi</label><input name="institution_name" class="form-control" maxlength="150" value="{{ old('institution_name', $team->institution_name) }}" placeholder="Contoh: Dinas Perhubungan"></div>
            <div class="col-12"><label class="form-label fw-semibold">Deskripsi</label><textarea name="description" class="form-control" rows="3" maxlength="2000" placeholder="Fokus atau tanggung jawab tim">{{ old('description', $team->description) }}</textarea></div>
            <div class="col-12"><label class="form-label fw-semibold">Anggota Officer <span class="text-danger">*</span></label><div class="text-muted small mb-2">Pilih satu atau lebih petugas. Petugas yang sama dapat dipilih di tim lain.</div>
                <div class="border rounded-3 p-3" style="max-height:360px;overflow:auto">
                    @php($selectedMembers = old('member_ids', $team->exists ? $team->members->pluck('id')->all() : []))
                    @forelse($officers as $officer)
                    <label class="d-flex align-items-center gap-3 py-2 border-bottom"><input class="form-check-input mt-0" type="checkbox" name="member_ids[]" value="{{ $officer->id }}" @checked(in_array($officer->id, $selectedMembers))><span><strong>{{ $officer->name }}</strong><small class="d-block text-muted">{{ $officer->email }}</small></span></label>
                    @empty<p class="text-muted mb-0">Belum ada akun dengan role Officer.</p>@endforelse
                </div>
            </div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $team->exists ? $team->is_active : true))><label class="form-check-label" for="is_active">Tim aktif dan dapat menerima penugasan</label></div></div>
        </div>
    </div><div class="card-footer bg-white border-0 px-4 pb-4 d-flex justify-content-end gap-2"><a href="{{ route('governance.teams.index') }}" class="btn btn-light">Batal</a><button class="btn text-white" style="background:#020d32">{{ $team->exists ? 'Simpan Perubahan' : 'Buat Tim' }}</button></div></form>
</div>
@endsection
