<div class="alert-notice">
    @foreach(["primary", "secondary", "success", "danger", "warning", "info", 'light', 'dark'] as $alert)
        @php
            $alertKey = "notice." . $alert;
        @endphp
        @if (session()->has($alertKey))
            <div class="alert alert-{{ $alert }}" role="alert">
                <strong>{{ session($alertKey) }}</strong>
            </div>
        @endif
    @endforeach
</div>
