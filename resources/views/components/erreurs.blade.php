@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <h6 class="alert-heading"><i class="fas fa-circle-exclamation me-2"></i>Merci de corriger les points suivants :</h6>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $erreur)
                <li>{{ $erreur }}</li>
            @endforeach
        </ul>
    </div>
@endif
