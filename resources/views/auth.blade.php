<x-layout><section class="card auth-card"><span class="eyebrow">SELAMAT DATANG</span><h1>{{ match($mode){'register'=>'Buat akun pribadi','forgot-password'=>'Lupa kata sandi','reset-password'=>'Atur kata sandi baru',default=>'Masuk ke akun Anda'} }}</h1><p class="muted">Satu akun untuk mengelola ruang dan meminjam barang.</p><form method="POST" action="/{{ $mode }}">@csrf
@if($mode==='register')<label>Nama<input name="name" value="{{ old('name') }}" required maxlength="150" autocomplete="name"></label>@endif
<label>Email<input name="email" type="email" value="{{ old('email',request('email')) }}" required autocomplete="email"></label>
@if($mode!=='forgot-password')<label>Kata sandi<input name="password" type="password" required autocomplete="{{ $mode==='login'?'current-password':'new-password' }}"></label>@endif
@if(in_array($mode,['register','reset-password']))<label>Konfirmasi kata sandi<input name="password_confirmation" type="password" required autocomplete="new-password"></label>@endif
@if($mode==='reset-password')<input type="hidden" name="token" value="{{ $token }}">@endif
<button class="primary" type="submit">{{ $mode==='login'?'Masuk':'Simpan dan lanjutkan' }}</button></form><p><a href="/register">Daftar</a> · <a href="/login">Masuk</a> · <a href="/forgot-password">Lupa kata sandi?</a></p></section></x-layout>
