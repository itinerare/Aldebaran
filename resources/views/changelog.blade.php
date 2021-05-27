@extends('layouts.app')

@section ('title') Changelog @endsection

@section('content')

<div class="borderhr mb-4">
<h1>Changelog</h1>
</div>

@if($changelogs->count())
    {!! $changelogs->render() !!}
    @foreach($changelogs as $changelog)
        <div class="card mb-4">
            <div class="card-header">
                <h2>{{ isset($changelog->name) ? $changelog->name : $changelog->created_at->toFormattedDateString() }}</h2>
                <small>
                    @if(isset($changelog->name))
                        Originally posted {{ $changelog->created_at->toFormattedDateString().' ・ ' }}
                    @endif
                    Last edited {{ $changelog->updated_at->toFormattedDateString() }}
                </small>
            </div>
            <div class="card-body">
                {!! $changelog->text !!}
            </div>
        </div>
    @endforeach
    {!! $changelogs->render() !!}
@else
    <p>No entries found. Check back later?</p>
@endif

@endsection
