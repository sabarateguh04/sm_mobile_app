@if(!empty($sbGrp))
    @foreach($sbGrp as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
@endif
