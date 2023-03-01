@extends('layouts.app')

@section('title')
    {{ $page->name }}
@endsection

@section('content')
    {!! breadcrumbs([$page->name => $page->key]) !!}

    <div class="borderhr mb-4">
        <x-admin-edit-button name="Page" :object="$page"/>
        <h1>{{ $page->name }}</h1>
        <p>Last updated {{ $page->updated_at->toFormattedDateString() }}</p>
    </div>

    {!! $page->text !!}
@endsection
