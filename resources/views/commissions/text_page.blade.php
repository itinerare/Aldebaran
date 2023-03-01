@extends('layouts.app')

@section('title')
    {{ $page->name }}
@endsection

@section('content')
    {!! breadcrumbs([$class->name . ' Commissions' => 'commissions/art', $page->name => $page->key]) !!}

    <div class="borderhr mb-4">
        <h1>
            {{ $page->name }}
            <a class="float-right btn btn-secondary ml-2" href="{{ url('commissions/' . $class->slug) }}">Back to Commission Info</a>
            <x-admin-edit-button name="Page" :object="$page"/>
        </h1>
        <p>Last updated {{ $page->updated_at->toFormattedDateString() }}</p>
    </div>

    {!! $page->text !!}
@endsection
