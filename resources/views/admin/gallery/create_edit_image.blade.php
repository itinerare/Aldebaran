@extends('admin.layout')

@section('admin-title')
    {{ $image->id ? 'Edit' : 'Create' }} Multimedia
@endsection

@section('admin-head-scripts')
    <script src="{{ asset('js/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('js/croppie.min.js') }}"></script>
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Pieces' => 'admin/data/pieces',
        'Edit Piece' => 'admin/data/pieces/edit/' . $piece->id,
        ($image->id ? 'Edit' : 'Create') . ' Multimedia' => $image->id ? 'admin/data/pieces/images/edit/' . $image->id : 'admin/data/pieces/images/create/' . $piece->id,
    ]) !!}

    <h1>{{ $image->id ? 'Edit' : 'Create' }} Multimedia
        @if ($image->id)
            <a href="#" class="btn btn-danger float-right delete-image-button">Delete Image</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $image->id ? 'admin/data/pieces/images/edit/' . $image->id : 'admin/data/pieces/images/create',
        'id' => 'imageForm',
        'files' => true,
    ]) !!}

    <h3>File</h3>

    <div class="form-group">
        @if ($image->id)
            <div class="card mb-2" id="existingImage">
                <div class="card-body text-center">
                    {{ $image->isMultimedia ? 'Fullsize/' : 'Watermarked/' }}Display:<br />
                    @if ($image->isVideo)
                        <video class="img-fluid p-2" style="max-height:60vh;" controls>
                            <source src="{{ url('admin/data/pieces/images/view/' . $image->id . '/display') }}" />
                        </video>
                    @else
                        <a href="{{ url('admin/data/pieces/images/view/' . $image->id . '/display') }}" class="image-link" title="Watermarked Image">
                            <img class="p-2" src="{{ url('admin/data/pieces/images/view/' . $image->id . '/display') }}" style="max-width:100%; max-height:60vh;" alt="Watermarked view" />
                        </a>
                    @endif
                </div>
                <div class="row">
                    @if (!$image->isMultimedia)
                        <div class="col-md-6 text-center">
                            Fullsize:<br />
                            <a href="{{ url('admin/data/pieces/images/view/' . $image->id . '/full') }}" class="image-link" title="Fullsize Image">
                                <img class="p-2" src="{{ url('admin/data/pieces/images/view/' . $image->id . '/full') }}" style="max-width:100%; max-height:60vh;" alt="Full-size view" />
                            </a>
                        </div>
                    @endif
                    <div class="col-md-{{ $image->isMultimedia ? '12' : '6' }} text-center">
                        Thumbnail:<br />
                        <a href="{{ url('admin/data/pieces/images/view/' . $image->id . '/thumb') }}" class="image-link" title="Thumbnail Image">
                            <img class="p-2" src="{{ url('admin/data/pieces/images/view/' . $image->id . '/thumb') }}" style="max-width:100%; max-height:60vh;" alt="Thumbnail view" />
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <div class="card mb-2 hide" id="imageContainer">
            <div class="card-body text-center">
                <img src="#" id="image" style="max-width:100%; max-height:60vh;" alt="Uploaded image preview" />
            </div>
        </div>
        <div class="card p-2">
            {!! Form::label('mainImage', 'Upload File') !!}
            {!! Form::file('image', ['id' => 'mainImage']) !!}
            <small>
                Files may be PNG, JPEG, WebP, GIF, MP4, or WebM and up to
                {{ min((int) ini_get('upload_max_filesize'), (int) ini_get('post_max_size'), '15') }}MB in size.<br />
                Note that watermarking is not supported for GIF, MP4, or WebM files.
            </small>
        </div>
    </div>

    <div id="watermarkOptions" class="{{ $image->isMultimedia ? 'hide' : '' }}">
        <div id="cropperOptions" class="form-group hide">
            {!! Form::checkbox('use_cropper', 1, 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
                'id' => 'useCropper',
            ]) !!}
            {!! Form::label('use_cropper', 'Use Thumbnail Cropper', ['class' => 'form-check-label ml-3']) !!} {!! add_help('You can use the image cropper (thumbnail dimensions can be adjusted in the site\'s config files), or let the site generate a thumbnail automatically.') !!}
        </div>
        <div class="card mb-3 hide" id="thumbnailCrop">
            <div class="card-body">
                <div id="cropSelect">Select an image to use the thumbnail cropper.</div>
                <img src="#" id="cropper" class="hide" />
                {!! Form::hidden('x0', null, ['id' => 'cropX0']) !!}
                {!! Form::hidden('x1', null, ['id' => 'cropX1']) !!}
                {!! Form::hidden('y0', null, ['id' => 'cropY0']) !!}
                {!! Form::hidden('y1', null, ['id' => 'cropY1']) !!}
            </div>
        </div>

        <div class="row">
            <div class="row no-gutters col-md-6 form-group">
                <div class="col-md-8 form-group">
                    {!! Form::label('watermark_scale', 'Watermark Scale') !!} {!! add_help('This adjusts the image watermark.') !!}
                    {!! Form::select('watermark_scale', ['.20' => '20%', '.30' => '30%', '.40' => '40%', '.50' => '50%', '.60' => '60%', '.70' => '70%'], $image->data['scale'] ?? '.30', [
                        'class' => 'form-control',
                        'placeholder' => 'Select a Scale',
                    ]) !!}
                </div>
                <div class="col-md-4 pl-2 form-group">
                    {!! Form::label('watermark_opacity', 'Opacity') !!}
                    {!! Form::select(
                        'watermark_opacity',
                        [
                            0 => '0%',
                            10 => '10%',
                            20 => '20%',
                            30 => '30%',
                            40 => '40%',
                            50 => '50%',
                            60 => '60%',
                            70 => '70%',
                            80 => '80%',
                            90 => '90%',
                            100 => '100%',
                        ],
                        $image->data['opacity'] ?? 30,
                        ['class' => 'form-control', 'placeholder' => 'Select an Opacity'],
                    ) !!}
                </div>
            </div>
            <div class="col-md-6 form-group">
                {!! Form::label('watermark_position', 'Watermark Position') !!}
                {!! Form::select(
                    'watermark_position',
                    [
                        'top-left' => 'Top Left',
                        'top' => 'Top',
                        'top-right' => 'Top Right',
                        'left' => 'Left',
                        'center' => 'Center',
                        'right' => 'Right',
                        'bottom-left' => 'Bottom Left',
                        'bottom' => 'Bottom',
                        'bottom-right' => 'Bottom Right',
                    ],
                    $image->data['position'] ?? 'bottom-right',
                    ['class' => 'form-control', 'placeholder' => 'Select a Position'],
                ) !!}
            </div>
            <div class="col-md-6 form-group">
                {!! Form::label('watermark_color', 'Watermark Color (Optional)') !!} {!! add_help('Should be a hex color code. Watermark defaults to black if not set.') !!}
                <div class="input-group cp">
                    {!! Form::text('watermark_color', $image->data['color'] ?? null, [
                        'class' => 'form-control',
                        'placeholder' => 'Enter a Color',
                    ]) !!}
                    <span class="input-group-append">
                        <span class="input-group-text colorpicker-input-addon"><i></i></span>
                    </span>
                </div>
            </div>
            <div class="row no-gutters col-md-6 form-group">
                <div class="col-md-8 form-group">
                    {!! Form::label('text_watermark', 'Text Watermark (Optional)') !!} {!! add_help('If set, this will add a repeating text watermark over the image.') !!}
                    {!! Form::select(
                        'text_watermark',
                        [
                            'generic' => 'Generic Work',
                            'personal' => 'Personal Work',
                            'gift' => 'Gift Work',
                            'commission' => 'Commissioned Work',
                        ],
                        $image->data['text_watermark'] ?? null,
                        ['class' => 'form-control', 'placeholder' => 'Select an Option'],
                    ) !!}
                </div>
                <div class="col-md-4 pl-2 form-group">
                    {!! Form::label('text_opacity', 'Opacity') !!}
                    {!! Form::select(
                        'text_opacity',
                        [
                            '.10' => '10%',
                            '.20' => '20%',
                            '.30' => '30%',
                            '.40' => '40%',
                            '.50' => '50%',
                            '.60' => '60%',
                            '.70' => '70%',
                            '.80' => '80%',
                            '.90' => '90%',
                            '1' => '100%',
                        ],
                        $image->data['text_opacity'] ?? '.30',
                        ['class' => 'form-control', 'placeholder' => 'Select an Option'],
                    ) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('image_scale', 'Image Scale (Optional)') !!} {!! add_help('If set, this resizes the image.') !!}
            {!! Form::select(
                'image_scale',
                [
                    '.10' => '10%',
                    '.20' => '20%',
                    '.30' => '30%',
                    '.40' => '40%',
                    '.50' => '50%',
                    '.60' => '60%',
                    '.70' => '70%',
                    '.80' => '80%',
                    '.90' => '90%',
                ],
                $image->data['image_scale'] ?? null,
                ['class' => 'form-control', 'placeholder' => 'Select an Image Scale'],
            ) !!}
        </div>

        <div class="row">
            @if ($image->id)
                <div id="regenWatermark" class="col-md form-group">
                    {!! Form::checkbox('regenerate_watermark', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('regenerate_watermark', 'Regenerate Watermarked Image', ['class' => 'form-check-label ml-3']) !!}
                </div>
            @endif
            <div class="col-md form-group">
                {!! Form::checkbox('watermark_image', 1, $image->data['watermarked'] ?? 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('watermark_image', 'Watermark Image', ['class' => 'form-check-label ml-3']) !!}
            </div>
        </div>
    </div>

    <h3>Other Information</h3>

    @if (!$image->id)
        <div class="form-group">
            {!! Form::hidden('piece_id', $piece->id, ['class' => 'form-control']) !!}
        </div>
    @endif

    <div class="form-group">
        {!! Form::label('description', 'Caption (Optional)') !!} {!! add_help('Should be brief.') !!}
        {!! Form::text('description', $image->description, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('alt_text', 'Alt. Text (Optional)') !!} {!! add_help('Will be used instead of the generic alt text for the image as well as listed alongside the piece\'s description.') !!}
        {!! Form::textarea('alt_text', $image->alt_text, ['class' => 'form-control']) !!}
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('is_primary_image', 1, $image->id ? $image->is_primary_image : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_primary_image', 'Is Primary Image', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Whether or not this is a primary image for the piece. Primary images are displayed front and center, while other images are sidelined but still visible.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_visible', 1, $image->id ? $image->is_visible : 1, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Hidden images are still visible to commissioners if the piece is attached to a commission.') !!}
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($image->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @include('gallery._lightbox_js')
    <script>
        $('.cp').colorpicker();

        $(document).ready(function() {
            $('.delete-image-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pieces/images/delete') }}/{{ $image->id }}",
                    'Delete Image');
            });

            var $image = $('#image');
            var $cropper = $('#cropper');

            // Cropper ////////////////////////////////////////////////////////////////////////////////////

            var $useCropper = $('#useCropper');
            var $thumbnailCrop = $('#thumbnailCrop');

            var useCropper = $useCropper.is(':checked');

            $useCropper.on('change', function(e) {
                useCropper = $useCropper.is(':checked');

                updateCropper();
            });

            function updateCropper() {
                if (useCropper) {
                    $thumbnailCrop.removeClass('hide');
                } else {
                    $thumbnailCrop.addClass('hide');
                }
            }

            // Croppie ////////////////////////////////////////////////////////////////////////////////////

            var thumbnailWidth = {{ config('aldebaran.settings.thumbnail_width') }};
            var thumbnailHeight = {{ config('aldebaran.settings.thumbnail_height') }};
            var c = null;
            var $x0 = $('#cropX0');
            var $y0 = $('#cropY0');
            var $x1 = $('#cropX1');
            var $y1 = $('#cropY1');
            var zoom = 0;

            function readURL(input) {
                console.log('Reading image URL...');
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        // Update image preview
                        $image.attr('src', e.target.result);
                        $('#existingImage').addClass('hide');

                        let $fileType = input.files[0].name.split('.').pop();
                        if ($fileType == 'mp4' || $fileType == 'webm') {
                            $('#watermarkOptions').addClass('hide');
                        } else {
                            if ($fileType == 'gif') {
                                $('#watermarkOptions').addClass('hide');
                            } else {
                                $('#watermarkOptions').removeClass('hide');
                            }

                            $('#imageContainer').removeClass('hide');

                            $cropper.attr('src', e.target.result);
                            c = new Croppie($cropper[0], {
                                viewport: {
                                    width: thumbnailWidth,
                                    height: thumbnailHeight
                                },
                                boundary: {
                                    width: thumbnailWidth + 100,
                                    height: thumbnailHeight + 100
                                },
                                update: function() {
                                    updateCropValues();
                                }
                            });
                            updateCropValues();
                            $('#cropSelect').addClass('hide');
                            $cropper.removeClass('hide');
                        }
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#mainImage").change(function() {
                $('#regenWatermark').addClass('hide');
                $('#cropperOptions').removeClass('hide');
                $thumbnailCrop.removeClass('hide');
                readURL(this);
            });

            function updateCropValues() {
                var values = c.get();
                console.log(values);
                //console.log([$x0.val(),$x1.val(),$y0.val(),$y1.val()]);
                $x0.val(values.points[0]);
                $y0.val(values.points[1]);
                $x1.val(values.points[2]);
                $y1.val(values.points[3]);
            }
        });

        $('.original.gallery-select').selectize();
    </script>
@endsection
