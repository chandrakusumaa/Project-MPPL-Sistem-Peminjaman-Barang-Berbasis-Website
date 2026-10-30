@if(session('status'))
    <div class="notice" role="status">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="errors" role="alert">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
