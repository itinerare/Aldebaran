<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceLiterature;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Program;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use App\Services\GalleryService;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class GalleryController extends Controller {
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
    public function getProjectIndex() {
        return view('admin.gallery.projects', [
            'projects' => Project::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create project page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateProject() {
        return view('admin.gallery.create_edit_project', [
            'project' => new Project,
        ]);
    }

    /**
     * Shows the edit project page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditProject($id) {
        $project = Project::find($id);
        if (!$project) {
            abort(404);
        }

        return view('admin.gallery.create_edit_project', [
            'project' => $project,
        ]);
    }

    /**
     * Creates or edits a project.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditProject(Request $request, GalleryService $service, $id = null) {
        $id ? $request->validate(Project::$updateRules) : $request->validate(Project::$createRules);
        $data = $request->only([
            'name', 'description', 'is_visible',
        ]);
        if ($id && $service->updateProject(Project::find($id), $data, $request->user())) {
            flash('Project updated successfully.')->success();
        } elseif (!$id && $project = $service->createProject($data, $request->user())) {
            flash('Project created successfully.')->success();

            return redirect()->to('admin/data/projects/edit/'.$project->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the project deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteProject($id) {
        $project = Project::find($id);

        return view('admin.gallery._delete_project', [
            'project' => $project,
        ]);
    }

    /**
     * Deletes a project.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteProject(Request $request, GalleryService $service, $id) {
        if ($id && $service->deleteProject(Project::find($id))) {
            flash('Project deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/projects');
    }

    /**
     * Sorts projects.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortProject(Request $request, GalleryService $service) {
        if ($service->sortProject($request->get('sort'))) {
            flash('Project order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /******************************************************************************
        PIECES
    *******************************************************************************/

    /**
     * Shows the piece index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPieceIndex(Request $request) {
        $query = Piece::query();
        $data = $request->only(['project_id', 'name', 'tags']);
        if (isset($data['project_id']) && $data['project_id'] != 'none') {
            $query->where('project_id', $data['project_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $query->whereIn('id', PieceTag::where('tag_id', $tag)->pluck('piece_id')->toArray());
            }
        }

        return view('admin.gallery.pieces', [
            'pieces'   => $query->orderByRaw('ifnull(timestamp, created_at) DESC')->paginate(20)->appends($request->query()),
            'tags'     => Tag::pluck('name', 'id'),
            'projects' => ['none' => 'Any Project'] + Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create piece page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreatePiece() {
        return view('admin.gallery.create_edit_piece', [
            'piece'    => new Piece,
            'projects' => Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'tags'     => Tag::orderBy('name')->pluck('name', 'id')->toArray(),
            'programs' => Program::orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit piece page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPiece($id) {
        $piece = Piece::find($id);
        if (!$piece) {
            abort(404);
        }

        return view('admin.gallery.create_edit_piece', [
            'piece'    => $piece,
            'projects' => Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'tags'     => Tag::orderBy('name')->pluck('name', 'id')->toArray(),
            'programs' => Program::orderBy('name')->get()->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a piece.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPiece(Request $request, GalleryService $service, $id = null) {
        $request->validate(Piece::$rules);
        $data = $request->only([
            'name', 'project_id', 'description', 'is_visible', 'timestamp', 'tags', 'programs', 'good_example',
        ]);
        if ($id && $service->updatePiece(Piece::find($id), $data, $request->user())) {
            flash('Piece updated successfully.')->success();
        } elseif (!$id && $piece = $service->createPiece($data, $request->user())) {
            flash('Piece created successfully.')->success();

            return redirect()->to('admin/data/pieces/edit/'.$piece->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the piece deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePiece($id) {
        $piece = Piece::find($id);

        return view('admin.gallery._delete_piece', [
            'piece' => $piece,
        ]);
    }

    /**
     * Deletes a piece.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePiece(Request $request, GalleryService $service, $id) {
        if ($id && $service->deletePiece(Piece::find($id))) {
            flash('Piece deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/pieces');
    }

    /**
     * Sorts piece images.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortPieceImages($id, Request $request, GalleryService $service) {
        if ($service->sortPieceImages($id, $request->get('sort'))) {
            flash('Image order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Sorts piece literatures.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortPieceLiteratures($id, Request $request, GalleryService $service) {
        if ($service->sortPieceLiteratures($id, $request->get('sort'))) {
            flash('Literature order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /******************************************************************************
        IMAGES
    *******************************************************************************/

    /**
     * Gets the image creation page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateImage($id) {
        $piece = Piece::find($id);
        if (!$piece) {
            abort(404);
        }

        return view('admin.gallery.create_edit_image', [
            'piece' => $piece,
            'image' => new PieceImage,
        ]);
    }

    /**
     * Gets the image edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditImage($id) {
        $image = PieceImage::find($id);
        if (!$image) {
            abort(404);
        }

        return view('admin.gallery.create_edit_image', [
            'image' => $image,
            'piece' => Piece::find($image->piece_id),
        ]);
    }

    /**
     * Display images (potentially in a specified format) for viewing
     * in the edit image panel.
     *
     * @param int    $id
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getImageFile($id, $type) {
        $image = PieceImage::where('id', $id)->first();
        if (!$image) {
            abort(404);
        }

        switch ($type) {
            case 'full':
                if (config('aldebaran.settings.image_formats.full') && config('aldebaran.settings.image_formats.admin_view')) {
                    $output = Image::make($image->imagePath.'/'.$image->fullsizeFileName);
                } else {
                    $output = $image->fullsizeUrl;
                }
                break;
            case 'display':
                if (config('aldebaran.settings.image_formats.display') && config('aldebaran.settings.image_formats.admin_view')) {
                    $output = Image::make($image->imagePath.'/'.$image->imageFileName);
                } else {
                    $output = $image->imageUrl;
                }
                break;
            case 'thumb':
                if (config('aldebaran.settings.image_formats.display') && config('aldebaran.settings.image_formats.admin_view')) {
                    $output = Image::make($image->imagePath.'/'.$image->thumbnailFileName);
                } else {
                    $output = $image->thumbnailUrl;
                }
                break;
        }
        if (!isset($output)) {
            abort(404);
        }

        if (is_object($output)) {
            return $output->response(config('aldebaran.settings.image_formats.admin_view'));
        }

        return redirect()->to($output);
    }

    /**
     * Creates and updates images.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditImage(Request $request, GalleryService $service, $id = null) {
        $id ? $request->validate(PieceImage::$updateRules) : $request->validate(PieceImage::$createRules);
        $data = $request->only([
            'image', 'description', 'is_primary_image', 'piece_id', 'alt_text', 'is_visible',
            'image_scale', 'watermark_scale', 'watermark_opacity', 'watermark_position', 'watermark_color',
            'regenerate_watermark', 'watermark_image', 'text_watermark', 'text_opacity',
        ]);

        if ($id && $service->updateImage(PieceImage::find($id), $data, $request->user())) {
            flash('Image updated successfully.')->success();
        } elseif (!$id && $image = $service->createImage($data, $request->user())) {
            flash('Image created successfully.')->success();

            return redirect()->to('admin/data/pieces/images/edit/'.$image->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the image deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteImage($id) {
        $image = PieceImage::find($id);
        if (!$image) {
            abort(404);
        }

        return view('admin.gallery._delete_image', [
            'image' => $image,
        ]);
    }

    /**
     * Deletes an image.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteImage(Request $request, GalleryService $service, $id) {
        $image = PieceImage::find($id);
        $piece = $image ? $image->piece : null;
        if ($id && $service->deletePieceImage($image)) {
            flash('Image deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/pieces/edit/'.$piece->id);
    }

    /******************************************************************************
        LITERATURES
    *******************************************************************************/

    /**
     * Gets the literature creation page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLiterature($id) {
        $piece = Piece::find($id);
        if (!$piece) {
            abort(404);
        }

        return view('admin.gallery.create_edit_literature', [
            'piece'      => $piece,
            'literature' => new PieceLiterature,
        ]);
    }

    /**
     * Gets the literature edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLiterature($id) {
        $literature = PieceLiterature::find($id);
        if (!$literature) {
            abort(404);
        }

        return view('admin.gallery.create_edit_literature', [
            'literature' => $literature,
            'piece'      => Piece::find($literature->piece_id),
        ]);
    }

    /**
     * Creates and updates literatures.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLiterature(Request $request, GalleryService $service, $id = null) {
        $id ? $request->validate(PieceLiterature::$updateRules) : $request->validate(PieceLiterature::$createRules);
        $data = $request->only([
            'piece_id', 'image', 'remove_image', 'text', 'is_primary', 'is_visible',
        ]);

        if ($id && $service->updateLiterature(PieceLiterature::find($id), $data, $request->user())) {
            flash('Literature updated successfully.')->success();
        } elseif (!$id && $image = $service->createLiterature($data, $request->user())) {
            flash('Literature created successfully.')->success();

            return redirect()->to('admin/data/pieces/literatures/edit/'.$image->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the literature deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLiterature($id) {
        $literature = PieceLiterature::find($id);
        if (!$literature) {
            abort(404);
        }

        return view('admin.gallery._delete_literature', [
            'literature' => $literature,
        ]);
    }

    /**
     * Deletes a literature.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLiterature(Request $request, GalleryService $service, $id) {
        $literature = PieceLiterature::find($id);
        $piece = $literature ? $literature->piece : null;
        if ($id && $service->deleteLiterature($literature)) {
            flash('Literature deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
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
    public function getTagIndex(Request $request) {
        return view('admin.gallery.tags', [
            'tags' => Tag::orderBy('name', 'ASC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create tag page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateTag() {
        return view('admin.gallery.create_edit_tag', [
            'tag' => new Tag,
        ]);
    }

    /**
     * Shows the edit tag page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditTag($id) {
        $tag = Tag::find($id);
        if (!$tag) {
            abort(404);
        }

        return view('admin.gallery.create_edit_tag', [
            'tag' => $tag,
        ]);
    }

    /**
     * Creates or edits a tag.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditTag(Request $request, GalleryService $service, $id = null) {
        $id ? $request->validate(Tag::$updateRules) : $request->validate(Tag::$createRules);
        $data = $request->only([
            'name', 'description', 'is_active', 'is_visible',
        ]);
        if ($id && $service->updateTag(Tag::find($id), $data, $request->user())) {
            flash('Tag updated successfully.')->success();
        } elseif (!$id && $tag = $service->createTag($data, $request->user())) {
            flash('Tag created successfully.')->success();

            return redirect()->to('admin/data/tags/edit/'.$tag->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the tag deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteTag($id) {
        $tag = Tag::find($id);

        return view('admin.gallery._delete_tag', [
            'tag' => $tag,
        ]);
    }

    /**
     * Deletes a tag.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteTag(Request $request, GalleryService $service, $id) {
        if ($id && $service->deleteTag(Tag::find($id))) {
            flash('Tag deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
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
    public function getProgramIndex(Request $request) {
        return view('admin.gallery.programs', [
            'programs' => Program::orderBy('name', 'ASC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create program page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateProgram() {
        return view('admin.gallery.create_edit_program', [
            'program' => new Program,
        ]);
    }

    /**
     * Shows the edit program page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditProgram($id) {
        $program = Program::find($id);
        if (!$program) {
            abort(404);
        }

        return view('admin.gallery.create_edit_program', [
            'program' => $program,
        ]);
    }

    /**
     * Creates or edits a program.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditProgram(Request $request, GalleryService $service, $id = null) {
        $id ? $request->validate(Program::$updateRules) : $request->validate(Program::$createRules);
        $data = $request->only([
            'name', 'image', 'is_visible', 'remove_image',
        ]);
        if ($id && $service->updateProgram(Program::find($id), $data, $request->user())) {
            flash('Program updated successfully.')->success();
        } elseif (!$id && $program = $service->createProgram($data, $request->user())) {
            flash('Program created successfully.')->success();

            return redirect()->to('admin/data/programs/edit/'.$program->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the program deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteProgram($id) {
        $program = Program::find($id);

        return view('admin.gallery._delete_program', [
            'program' => $program,
        ]);
    }

    /**
     * Deletes a program.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteProgram(Request $request, GalleryService $service, $id) {
        if ($id && $service->deleteProgram(Program::find($id))) {
            flash('Program deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/programs');
    }
}
