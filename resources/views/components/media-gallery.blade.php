@props(['quoteRequest'])
@if($quoteRequest->media->isNotEmpty())
    <div class="media-gallery">
        @foreach($quoteRequest->media as $media)
            @php $url = route('quote-requests.media', [$quoteRequest, $media]); @endphp
            @if($media->isVideo())
                <video src="{{ $url }}" controls preload="metadata" playsinline></video>
            @elseif($media->isDisplayableImage())
                <a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="Photo from the homeowner" loading="lazy"></a>
            @else
                <a class="media-file" href="{{ $url }}">{{ $media->original_name }}<span class="muted small">Download</span></a>
            @endif
        @endforeach
    </div>
@endif
