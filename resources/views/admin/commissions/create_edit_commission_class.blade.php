@extends('admin.layout')

@section('admin-title')
    Commission Classes
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Commission Classes' => 'admin/data/commissions/classes',
        ($class->id ? 'Edit' : 'Create') . ' Class' => $class->id ? 'admin/data/commissions/classes/edit/' . $class->id : 'admin/data/commissions/classes/create',
    ]) !!}

    <h1>{{ $class->id ? 'Edit' : 'Create' }} Class
        @if ($class->id)
            <a href="#" class="btn btn-danger float-right delete-class-button">Delete Class</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $class->id ? 'admin/data/commissions/classes/edit/' . $class->id : 'admin/data/commissions/classes/create',
    ]) !!}

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('name', 'Name') !!}
                {!! Form::text('name', $class->name, ['class' => 'form-control', 'required']) !!}
            </div>
        </div>
        @if ($class->id)
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('slug', 'Slug') !!}
                    {!! Form::text('slug', $class->slug, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_active', 1, $class->id ? $class->is_active : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, visitors will not be able to see this class. It will remain visible to you, however, including its queue etc. However, simply closing commissions for this class is recommended instead.') !!}
    </div>

    @if ($class->id)
        <h2>Custom Pages</h2>
        <p>While all classes have two set pages (Terms of Service and Info), you can specify custom pages here as well.
            These pages can be edited via the <a href="{{ url('admin/pages') }}">text pages admin panel</a>. The URL of
            each is also displayed, for convenience, as you will need to link to any pages here manually.</p>

        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="add-page">Add Page</a>
        </div>
        <div id="pageList">
            @if (isset($class->data['pages']))
                @foreach ($class->data['pages'] as $key => $page)
                    <div class="card mb-2">
                        <div class="card-body">
                            <a href="#" class="float-right remove-page btn btn-danger mb-2">×</a>
                            {!! Form::hidden('page_id[]', $key) !!}
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        {!! Form::label('page_title[]', 'Page Title') !!}
                                        {!! Form::text('page_title[]', $page['title'], ['class' => 'form-control', 'required']) !!}
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        {!! Form::label('page_key[]', 'Page Key') !!}
                                        {!! Form::text('page_key[]', $page['key'], ['class' => 'form-control', 'required']) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('page_url[]', 'URL') !!}
                                        {!! Form::text('page_url[]', url('commissions/' . $class->slug . '/' . $page['key']), [
                                            'class' => 'form-control',
                                            'disabled',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <h2>Form Fields</h2>
        <p>These fields will be used to populate the commission request form for this class if the category or type have no
            set fields, or if they are optionally included in either's form.</p>

        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="add-field">Add Field</a>
        </div>
        <div id="fieldList">
            @if (isset($class->data['fields']))
                @foreach ($class->data['fields'] as $key => $field)
                    @include('admin.commissions._field_builder_entry', ['key' => $key, 'field' => $field])
                @endforeach
            @endif
        </div>
    @endif

    @if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') || config('aldebaran.commissions.payment_processors.paypal.integration.enabled'))
        <h2>Invoice Information</h2>
        <p>
            This will be used to populate product information when creating invoices for this commission class. You can specify more specific information per commission category and type as well as per individual commission;
            however, if a field is unset for any of those, the information set here will be used instead. Consequently, it's required that this information be set.
        </p>
        @include('admin.commissions._invoice_fields', ['object' => $class, 'require' => true])
    @endif

    <div class="text-right">
        {!! Form::submit($class->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div class="page-row hide mb-2">
        <div class="card mb-2">
            <div class="card-body">
                <a href="#" class="float-right remove-page btn btn-danger mb-2">×</a>
                {!! Form::hidden('page_id[]', null) !!}
                <div class="row">
                    <div class="col-md">
                        <div class="form-group">
                            {!! Form::label('page_title[]', 'Page Title') !!}
                            {!! Form::text('page_title[]', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-group">
                            {!! Form::label('page_key[]', 'Page Key') !!}
                            {!! Form::text('page_key[]', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="field-row hide mb-2">
        @include('admin.commissions._field_builder_row')
    </div>

@endsection

@section('scripts')
    @parent

    @include('admin.commissions._field_builder_js')

    <script>
        $(document).ready(function() {
            $('.delete-class-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/commissions/classes/delete') }}/{{ $class->id }}",
                    'Delete Class');
            });

            $('#add-page').on('click', function(e) {
                e.preventDefault();
                addPageRow();
            });
            $('.remove-page').on('click', function(e) {
                e.preventDefault();
                removePageRow($(this));
            })

            function addPageRow() {
                var $clone = $('.page-row').clone();
                $('#pageList').append($clone);
                $clone.removeClass('hide page-row');
                $clone.find('.remove-page').on('click', function(e) {
                    e.preventDefault();
                    removePageRow($(this));
                })
            }

            function removePageRow($trigger) {
                $trigger.parent().parent().remove();
            }
        });
    </script>
@endsection
