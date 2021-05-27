@if($project)
    {!! Form::open(['url' => 'admin/data/projects/delete/'.$project->id]) !!}

    <p>You are about to delete the project <strong>{{ $project->name }}</strong>. This is not reversible. If pieces in this project exist, you will not be able to delete this project.</p>
    <p>Are you sure you want to delete <strong>{{ $project->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Project', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid project selected.
@endif
