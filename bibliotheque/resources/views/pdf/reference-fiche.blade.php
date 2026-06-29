<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reference->title }}</title>
    <style>
        @page { margin: 30px 35px; }
        body {
            font-family: 'DejaVu Serif', Georgia, serif;
            color: #1a1a1a;
            line-height: 1.5;
            font-size: 11pt;
        }
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #b8860b;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            color: #1a3a28;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }
        .header .subtitle-line {
            font-size: 9pt;
            color: #666;
            font-style: italic;
        }
        .header .ornament {
            color: #b8860b;
            font-size: 14pt;
            margin: 5px 0;
        }
        .title-block {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-block h2 {
            font-size: 16pt;
            color: #1a3a28;
            margin: 0 0 5px 0;
            line-height: 1.3;
        }
        .title-block .subtitle {
            font-size: 11pt;
            color: #555;
            font-style: italic;
            margin: 0;
        }
        .title-block .authors {
            font-size: 11pt;
            color: #333;
            margin-top: 8px;
        }
        .meta-grid {
            display: table;
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        .meta-row {
            display: table-row;
        }
        .meta-label {
            display: table-cell;
            width: 130px;
            font-size: 9pt;
            font-weight: 600;
            color: #1a3a28;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 10px 4px 0;
            border-bottom: 1px dotted #ddd;
        }
        .meta-value {
            display: table-cell;
            font-size: 10pt;
            color: #333;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
        }
        .abstract-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f6f0;
            border-left: 3px solid #b8860b;
        }
        .abstract-section strong {
            display: block;
            font-size: 9pt;
            color: #1a3a28;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .abstract-section p {
            font-size: 10pt;
            font-style: italic;
            color: #444;
            margin: 0;
            line-height: 1.6;
        }
        .keywords-section {
            margin: 15px 0;
        }
        .keywords-section strong {
            font-size: 9pt;
            color: #1a3a28;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .keywords-list {
            margin-top: 5px;
        }
        .keyword-tag {
            display: inline;
            font-size: 9pt;
            color: #555;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
        }
        .footer .note {
            font-size: 8pt;
            color: #888;
            font-style: italic;
            margin-bottom: 3px;
        }
        .footer .brand {
            font-size: 9pt;
            color: #1a3a28;
            font-weight: 600;
        }
        .footer .date {
            font-size: 7pt;
            color: #aaa;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>BIBLIOTHÈQUE NUMÉRIQUE</h1>
        <div class="ornament">✦ &nbsp; &bull; &nbsp; ✦</div>
        <div class="subtitle-line">Fiche documentaire</div>
    </div>

    <div class="title-block">
        <h2>{{ $reference->title }}</h2>
        @if($reference->subtitle)
            <p class="subtitle">{{ $reference->subtitle }}</p>
        @endif
        @if($reference->authors->count())
            <p class="authors">
                {{ $reference->authors->pluck('full_name')->implode(', ') }}
            </p>
        @endif
    </div>

    <div class="meta-grid">
        @if($reference->isbn)
        <div class="meta-row">
            <span class="meta-label">ISBN</span>
            <span class="meta-value">{{ $reference->isbn }}</span>
        </div>
        @endif
        @if($reference->publication_year)
        <div class="meta-row">
            <span class="meta-label">Année</span>
            <span class="meta-value">{{ $reference->publication_year }}</span>
        </div>
        @endif
        @if($reference->language)
        <div class="meta-row">
            <span class="meta-label">Langue</span>
            <span class="meta-value">
                @switch($reference->language)
                    @case('fr') Français @break
                    @case('en') Anglais @break
                    @case('autre') Autre @break
                    @default {{ $reference->language }}
                @endswitch
            </span>
        </div>
        @endif
        @if($reference->documentType)
        <div class="meta-row">
            <span class="meta-label">Type</span>
            <span class="meta-value">{{ $reference->documentType->label ?? $reference->documentType->name }}</span>
        </div>
        @endif
        @if($reference->category)
        <div class="meta-row">
            <span class="meta-label">Catégorie</span>
            <span class="meta-value">{{ $reference->category->name }}</span>
        </div>
        @endif
        @if($reference->publisher)
        <div class="meta-row">
            <span class="meta-label">Éditeur</span>
            <span class="meta-value">{{ $reference->publisher->name }}@if($reference->publisher->country) ({{ $reference->publisher->country }})@endif</span>
        </div>
        @endif
        @if($reference->pages)
        <div class="meta-row">
            <span class="meta-label">Pages</span>
            <span class="meta-value">{{ $reference->pages }}</span>
        </div>
        @endif
    </div>

    @if($reference->abstract)
    <div class="abstract-section">
        <strong>Résumé</strong>
        <p>{{ $reference->abstract }}</p>
    </div>
    @endif

    @if($reference->keywords->count())
    <div class="keywords-section">
        <strong>Mots-clés</strong>
        <div class="keywords-list">
            @foreach($reference->keywords as $i => $keyword)
                <span class="keyword-tag">{{ $keyword->name }}@if(!$loop->last), @endif</span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        <p class="note">Document consultable en ligne sur la Bibliothèque Numérique.</p>
        <p class="note">Ce document est protégé. Sa diffusion intégrale est interdite.</p>
        <p class="brand">Bibliothèque Numérique &mdash; www.bibliotheque-numerique.ci</p>
        <p class="date">Fiche générée le {{ now()->format('d/m/Y') }}</p>
    </div>

</body>
</html>
