<div class="row mb-2">
    <div class="col-md-4"><h5>Reference(s)</h5></div>
    <div class="col-md">{!! isset($commission->description['references']) ? nl2br(htmlentities($commission->description['references'])) : '-' !!}</div>
</div>

<div class="row mb-2">
    <div class="col-md-4"><h5>Desired pose(s), attitude(s)/Expression(s), etc</h5></div>
    <div class="col-md">{!! isset($commission->description['details']) ? nl2br(htmlentities($commission->description['details'])) : '-' !!}</div>
</div>
