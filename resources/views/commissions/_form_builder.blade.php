@if(View::exists('commissions.type_forms._'.$categoryName.'_'.$typeName))
    @include('commissions.type_forms._'.$categoryName.'_'.$typeName)
@elseif(View::exists('commissions.type_forms._'.$categoryName))
    @include('commissions.type_forms._'.$categoryName)
@elseif(View::exists('commissions.type_forms._'.$type.'_basic'))
    @include('commissions.type_forms._'.$type.'_basic')
@endif
