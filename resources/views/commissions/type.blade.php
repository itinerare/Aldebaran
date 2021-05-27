@extends('layouts.app')

@section('title') {{ $type->name }} @endsection

@section('content')
{!! breadcrumbs([$type->category->type.' Commissions' => 'commissions/'.$type->category->type,$type->name.' Commissions' => 'commissions/types/'.$type->key]) !!}

<div class="borderhr mb-4">
<h1>{{ $type->name }} Commmissions</h1>
<p>Last updated {{ $type->updated_at->toFormattedDateString() }}</p>
</div>

<div class="card card-body mb-4">
    @include('commissions._type_info', ['type' => $type, 'source' => $type->key, 'category' => $type->category])
</div>

@endsection
