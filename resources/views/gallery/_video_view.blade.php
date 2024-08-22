<div class="gallery-popup" style="max-width: 90vw; width:{{ $image->data['content_width'] ?? 1000 }}px; margin-left: auto; margin-right: auto;">
    <div class="text-right">
        <a title="Close (Esc)" class="mfp-close position-relative text-light">×</a>
    </div>
    <div class="text-center">
        <video class="img-fluid" style="max-height:60vh; box-shadow: 0 0 10px black;" controls>
            <source src="{{ $image->imageUrl }}" />
        </video>
        @isset($image->description)
            <div class="text-left text-light mx-1">
                {!! $image->description !!}
            </div>
            @endif
        </div>
    </div>
