@extends('layouts.app')

@section('title', 'Lire: ' . $livre->titre)

@section('content')
    <style>
        /* Arrière-plan du conteneur principal */
        .reader-wrapper {
            background-color: #FAF3E0;
            min-height: 100vh;
            padding: 20px 0;
        }

        /* En-tête du livre */
        .book-header-card {
            border-left: 5px solid #D4AF37;
        }

        .book-title {
            color: #5D4037;
            font-weight: bold;
        }

        /* Lecteur PDF - Header */
        .reader-header {
            background-color: #5D4037 !important;
            color: #FAF3E0 !important;
            border-bottom: 3px solid #D4AF37 !important;
        }

        /* Barre d'outils (Lecteur) */
        .reader-toolbar {
            background-color: #f1ebd8 !important;
            /* Une variante crème légèrement plus sombre */
            border-bottom: 1px solid #D4AF37;
        }

        /* Boutons personnalisés Or */
        .btn-gold-outline {
            color: #5D4037;
            border-color: #D4AF37;
            background-color: transparent;
            transition: all 0.3s;
        }

        .btn-gold-outline:hover {
            background-color: #D4AF37;
            color: #5D4037;
            border-color: #D4AF37;
        }

        .btn-gold-fill {
            background-color: #D4AF37;
            color: #5D4037;
            border: none;
            font-weight: 600;
        }

        .btn-gold-fill:hover {
            background-color: #5D4037;
            color: #FAF3E0;
        }

        /* Conteneur du PDF */
        .pdf-container {
            background-color: #3e3a35;
            /* Gris/brun très sombre pour faire ressortir le papier */
            text-align: center;
            min-height: 600px;
        }

        #pdf-canvas {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            margin: 30px auto;
            display: block;
            border: 1px solid #D4AF37;
        }

        /* Loading Spinner */
        .text-gold {
            color: #D4AF37 !important;
        }
    </style>

    <div class="reader-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card book-header-card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1 book-title">{{ $livre->titre }}</h4>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-feather-alt me-1"></i> {{ $livre->auteur }}
                                    <span class="mx-2">•</span>
                                    <i class="far fa-calendar-alt me-1"></i> {{ $livre->annee_publication }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('livres.show', $livre) }}" class="btn btn-outline-dark">
                                    <i class="fas fa-arrow-left me-1"></i> Retour à la fiche
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header reader-header">
                            <h5 class="mb-0">
                                <i class="fas fa-book-reader me-2"></i> Salle de lecture virtuelle
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="reader-toolbar p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-gold-outline" onclick="zoomOut()"
                                                title="Zoom arrière">
                                                <i class="fas fa-search-minus"></i>
                                            </button>
                                            <button type="button" class="btn btn-gold-outline" onclick="resetZoom()"
                                                title="Taille réelle">
                                                <i class="fas fa-compress"></i>
                                            </button>
                                            <button type="button" class="btn btn-gold-outline" onclick="zoomIn()"
                                                title="Zoom avant">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                        </div>
                                        <div class="btn-group ms-3" role="group">
                                            <button type="button" class="btn btn-gold-outline" onclick="previousPage()">
                                                <i class="fas fa-chevron-left me-1"></i> Précédent
                                            </button>
                                            <button type="button" class="btn btn-gold-outline" onclick="nextPage()">
                                                Suivant <i class="fas fa-chevron-right ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <span class="me-3 fw-bold" style="color: #5D4037;">
                                                Page <span id="page-num" class="badge bg-dark">1</span> sur <span
                                                    id="page-count">?</span>
                                            </span>
                                            <button type="button" class="btn btn-gold-fill" onclick="toggleFullscreen()">
                                                <i class="fas fa-expand me-1"></i> Plein écran
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pdf-container" style="height: 80vh; overflow: auto;">
                                <canvas id="pdf-canvas" class="mx-auto"></canvas>

                                <div id="loading" class="text-center p-5">
                                    <div class="spinner-border text-gold" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-3 text-white">Préparation de votre ouvrage...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        /* ... (Votre logique JavaScript existante reste la même) ... */
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.5,
            canvas = document.getElementById('pdf-canvas'),
            ctx = canvas.getContext('2d');

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then(function (page) {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                const renderContext = { canvasContext: ctx, viewport: viewport };
                const renderTask = page.render(renderContext);
                renderTask.promise.then(function () {
                    pageRendering = false;
                    document.getElementById('page-num').textContent = num;
                    if (pageNumPending !== null) { renderPage(pageNumPending); pageNumPending = null; }
                });
            });
            document.getElementById('page-count').textContent = pdfDoc.numPages;
        }

        function queueRenderPage(num) {
            if (pageRendering) { pageNumPending = num; } else { renderPage(num); }
        }

        function previousPage() { if (pageNum <= 1) return; pageNum--; queueRenderPage(pageNum); }
        function nextPage() { if (pageNum >= pdfDoc.numPages) return; pageNum++; queueRenderPage(pageNum); }
        function zoomIn() { scale = Math.min(scale + 0.25, 3.0); queueRenderPage(pageNum); }
        function zoomOut() { scale = Math.max(scale - 0.25, 0.5); queueRenderPage(pageNum); }
        function resetZoom() { scale = 1.5; queueRenderPage(pageNum); }

        function toggleFullscreen() {
            const pdfContainer = document.querySelector('.pdf-container');
            if (!document.fullscreenElement) {
                pdfContainer.requestFullscreen().catch(err => console.log(err));
            } else {
                document.exitFullscreen();
            }
        }

        function loadPDF() {
            const url = '{{ Storage::url($livre->fichier_numerique) }}';
            pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('page-count').textContent = pdfDoc.numPages;
                document.getElementById('loading').style.display = 'none';
                renderPage(pageNum);
            }).catch(function (error) {
                document.getElementById('loading').innerHTML =
                    '<div class="alert alert-danger">Impossible de charger le fichier PDF.</div>';
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') previousPage();
            else if (e.key === 'ArrowRight') nextPage();
        });

        document.addEventListener('DOMContentLoaded', loadPDF);
    </script>
@endsection