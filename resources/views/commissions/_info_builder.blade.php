@if(View::exists('commissions.type_info._'.$categoryName.'_'.$typeName))
    @include('commissions.type_info._'.$categoryName.'_'.$typeName)
@elseif(View::exists('commissions.type_forms._'.$categoryName))
    @include('commissions.type_info._'.$categoryName)
@elseif(View::exists('commissions.type_forms._'.$type.'_basic'))
    @include('commissions.type_info._'.$type.'_basic')
@endif
