<?php

namespace App\Http\Controllers\Admin\Data;

use Auth;

use App\Models\Gallery\Project;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\Tag;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Program;
use App\Services\GalleryService;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GalleryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Gallery Data Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of gallery data.
    |
    */

    /******************************************************************************
        PROJECTS
    *******************************************************************************/

    /**
     * Shows the project index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getProjectIndex()
    {
        return view('admin.gallery.projects', [
            'projects' => Project::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create project page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateProject()
    {
        return view('admin.gallery.create_edit_project', [
            'project' => new Project
        ]);
    }

    /**
     * Shows the edit project page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditProject($id)
    {
        $project = Project::find($id);
        if(!$project) abort(404);
        return view('admin.gallery.create_edit_project', [
            'project' => $project
        ]);
    }

    /**
     * Creates or edits a project.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditProject(Request $request, GalleryService $service, $id = null)
    {
        $id ? $request->validate(Project::$updateRules) : $request->validate(Project::$createRules);
        $data = $request->only([
            'name', 'description', 'is_visible'
        ]);
        if($id && $service->updateProject(Project::find($id), $data, Auth::user())) {
            flash('Project updated successfully.')->success();
        }
        else if (!$id && $project = $service->createProject($data, Auth::user())) {
            flash('Project created successfully.')->success();
            return redirect()->to('admin/data/projects/edit/'.$project->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the project deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteProject($id)
    {
        $project = Project::find($id);
        return view('admin.gallery._delete_project', [
            'project' => $project,
        ]);
    }

    /**
     * Deletes a project.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int                          $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteProject(Request $request, GalleryService $service, $id)
    {
        if($id && $service->deleteProject(Project::find($id))) {
            flash('Project deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/projects');
    }

    /**
     * Sorts projects.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortProject(Request $request, GalleryService $service)
    {
        if($service->sortProject($request->get('sort'))) {
            flash('Project order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /******************************************************************************
        PIECES
    *******************************************************************************/

    /**
     * Shows the piece index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPieceIndex(Request $request)
    {
        $query = Piece::query();
        $data = $request->only(['project_id', 'name', 'tags']);
        if(isset($data['project_id']) && $data['project_id'] != 'none')
            $query->where('project_id', $data['project_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        if(isset($data['tags']))
            foreach($data['tags'] as $tag)
                $query->whereIn('id', PieceTag::where('tag_id', $tag)->pluck('piece_id')->toArray());

        return view('admin.gallery.pieces', [
            'pieces' => $query->orderByRaw('ifnull(timestamp, created_at) DESC')->paginate(20)->appends($request->query()),
            'tags' => Tag::pluck('name', 'id'),
            'projects' => ['none' => 'Any Project'] + Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the create piece page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreatePiece()
    {
        return view('admin.gallery.create_edit_piece', [
            'piece' => new Piece,
            'projects' => Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'tags' => Tag::orderBy('name')->pluck('name', 'id')->toArray(),
            'programs' => Program::orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the edit piece page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPiece($id)
    {
        $piece = Piece::find($id);
        if(!$piece) abort(404);
        return view('admin.gallery.create_edit_piece', [
            'piece' => $piece,
            'projects' => Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'tags' => Tag::orderBy('name')->pluck('name', 'id')->toArray(),
            'programs' => Program::orderBy('name')->get()->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Creates or edits a piece.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPiece(Request $request, GalleryService $service, $id = null)
    {
        $request->validate(Piece::$rules);
        $data = $request->only([
            'name', 'project_id', 'description', 'is_visible', 'timestamp', 'tags', 'programs', 'good_example'
        ]);
        if($id && $service->updatePiece(Piece::find($id), $data, Auth::user())) {
            flash('Piece updated successfully.')->success();
        }
        else if (!$id && $piece = $service->createPiece($data, Auth::user())) {
            flash('Piece created successfully.')->success();
            return redirect()->to('admin/data/pieces/edit/'.$piece->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the piece deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePiece($id)
    {
        $piece = Piece::find($id);
        return view('admin.gallery._delete_piece', [
            'piece' => $piece,
        ]);
    }

    /**
     * Deletes a piece.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int                          $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePiece(Request $request, GalleryService $service, $id)
    {
        if($id && $service->deletePiece(Piece::find($id))) {
            flash('Piece deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/pieces');
    }

    /**
     * Sorts piece images.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortPieceImages($id, Request $request, GalleryService $service)
    {
        if($service->sortPieceImages($id, $request->get('sort'))) {
            flash('Image order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /******************************************************************************
        IMAGES
    *******************************************************************************/

    /**
     * Gets the image creation page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateImage(GalleryService $service, $id)
    {
        $piece = Piece::find($id);
        return view('admin.gallery.create_edit_image', [
            'piece' => $piece,
            'image' => new PieceImage
        ]);
    }

    /**
     * Gets the image edit page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditImage($id)
    {
        $image = PieceImage::find($id);
        if(!$image) abort(404);
        return view('admin.gallery.create_edit_image', [
            'image' => $image,
            'piece' => Piece::find($image->piece_id)
        ]);
    }

    /**
     * Creates and updates images.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int                          $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditImage(Request $request, GalleryService $service, $id = null)
    {
        $id ? $request->validate(PieceImage::$updateRules) : $request->validate(PieceImage::$createRules);
        $data = $request->only([
            'image', 'description', 'is_primary_image', 'piece_id', 'is_visible', 'image_scale',
            'watermark_scale', 'watermark_opacity', 'watermark_position', 'watermark_color',
            'regenerate_watermark', 'watermark_image', 'text_watermark', 'text_opacity'
        ]);

        if($id && $service->updateImage(PieceImage::find($id), $data, Auth::user())) {
            flash('Image updated successfully.')->success();
        }
        else if (!$id && $image = $service->createImage($data, Auth::user())) {
            flash('Image created successfully.')->success();
            return redirect()->to('admin/data/pieces/images/edit/'.$image->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();

    }

    /**
     * Gets the image deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteImage($id)
    {
        $image = PieceImage::find($id);
        return view('admin.gallery._delete_image', [
            'image' => $image,
        ]);
    }

    /**
     * Deletes an image.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\GalleryService    $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteImage(Request $request, GalleryService $service, $id)
    {
        $image = PieceImage::find($id); $piece = $image->piece;
        if($id && $service->deletePieceImage($image)) {
            flash('Image deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/pieces/edit/'.$piece->id);
    }

    /******************************************************************************
        TAGS
    *******************************************************************************/

    /**
     * Shows the tag index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTagIndex()
    {
        return view('admin.gallery.tags', [
            'tags' => Tag::orderBy('name', 'ASC')->paginate(20)
        ]);
    }

    /**
     * Shows the create tag page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateTag()
    {
        return view('admin.gallery.create_edit_tag', [
            'tag' => new Tag
        ]);
    }

    /**
     * Shows the edit tag page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditTag($id)
    {
        $tag = Tag::find($id);
        if(!$tag) abort(404);
        return view('admin.gallery.create_edit_tag', [
            'tag' => $tag
        ]);
    }

    /**
     * Creates or edits a tag.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditTag(Request $request, GalleryService $service, $id = null)
    {
        $id ? $request->validate(Tag::$updateRules) : $request->validate(Tag::$createRules);
        $data = $request->only([
            'name', 'description', 'is_active', 'is_visible'
        ]);
        if($id && $service->updateTag(Tag::find($id), $data, Auth::user())) {
            flash('Tag updated successfully.')->success();
        }
        else if (!$id && $tag = $service->createTag($data, Auth::user())) {
            flash('Tag created successfully.')->success();
            return redirect()->to('admin/data/tags/edit/'.$tag->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the tag deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteTag($id)
    {
        $tag = Tag::find($id);
        return view('admin.gallery._delete_tag', [
            'tag' => $tag,
        ]);
    }

    /**
     * Deletes a tag.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int                          $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteTag(Request $request, GalleryService $service, $id)
    {
        if($id && $service->deleteTag(Tag::find($id))) {
            flash('Tag deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/tags');
    }

    /******************************************************************************
        PROGRAMS
    *******************************************************************************/

    /**
     * Shows the program index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getProgramIndex()
    {
        return view('admin.gallery.programs', [
            'programs' => Program::orderBy('name', 'ASC')->paginate(20)
        ]);
    }

    /**
     * Shows the create program page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateProgram()
    {
        return view('admin.gallery.create_edit_program', [
            'program' => new Program
        ]);
    }

    /**
     * Shows the edit program page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditProgram($id)
    {
        $program = Program::find($id);
        if(!$program) abort(404);
        return view('admin.gallery.create_edit_program', [
            'program' => $program
        ]);
    }

    /**
     * Creates or edits a program.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditProgram(Request $request, GalleryService $service, $id = null)
    {
        $id ? $request->validate(Program::$updateRules) : $request->validate(Program::$createRules);
        $data = $request->only([
            'name', 'image', 'is_visible'
        ]);
        if($id && $service->updateProgram(Program::find($id), $data, Auth::user())) {
            flash('Program updated successfully.')->success();
        }
        else if (!$id && $program = $service->createProgram($data, Auth::user())) {
            flash('Program created successfully.')->success();
            return redirect()->to('admin/data/programs/edit/'.$program->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the program deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteProgram($id)
    {
        $program = Program::find($id);
        return view('admin.gallery._delete_program', [
            'program' => $program,
        ]);
    }

    /**
     * Deletes a program.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\GalleryService  $service
     * @param  int                          $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteProgram(Request $request, GalleryService $service, $id)
    {
        if($id && $service->deleteProgram(Program::find($id))) {
            flash('Program deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/programs');
    }

}