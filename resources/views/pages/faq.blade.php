@extends('layouts.public')
@section('title', 'Questions fréquentes — BiblioteqUniv')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5">
                <div class="pastille mx-auto mb-3"><i class="fas fa-circle-question"></i></div>
                <h1 class="fw-bold">Questions fréquentes</h1>
                <p class="text-muted mb-0">Les réponses aux questions les plus posées au guichet.</p>
            </div>

            <div class="accordion" id="accordeon-faq">
                @foreach($questions as $index => $item)
                    <div class="accordion-item carte mb-2 border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#q-{{ $index }}">
                                {{ $item['question'] }}
                            </button>
                        </h2>
                        <div id="q-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                             data-bs-parent="#accordeon-faq">
                            <div class="accordion-body text-muted">{{ $item['reponse'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="carte p-4 mt-4 text-center">
                <p class="mb-3">Vous ne trouvez pas votre réponse ?</p>
                <a href="{{ route('aide') }}" class="btn btn-marque"><i class="fas fa-headset me-1"></i> Contacter l'équipe</a>
            </div>
        </div>
    </div>
</div>
@endsection
