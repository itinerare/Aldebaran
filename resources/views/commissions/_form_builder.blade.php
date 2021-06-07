@foreach([$categoryName.'_'.$typeName, $categoryName, 'basic'] as $section)
    @if(Config::get('itinerare.comm_types.'.$type.'.forms.'.$section) != null)
        @foreach(Config::get('itinerare.comm_types.'.$type.'.forms.'.$section) as $key=>$field)
            @if($key != 'includes')
                @include('commissions._'.($form ? 'form' : 'info').'_builder_components', ['key' => $key, 'field' => $field])
            @elseif($key == 'includes')
                @foreach(Config::get('itinerare.comm_types.'.$type.'.forms.'.$section.'.includes') as $include)
                    @foreach(Config::get('itinerare.comm_types.'.$type.'.forms.'.$include) as $key=>$field)
                        @include('commissions._'.($form ? 'form' : 'info').'_builder_components', ['key' => $key, 'field' => $field])
                    @endforeach
                @endforeach
            @endif
        @endforeach
        @break
    @endif
@endforeach
