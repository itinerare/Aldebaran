<?php

namespace App\Http\Controllers;

use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use App\Models\TextPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Gallery Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing of the gallery, projects, and individual pieces.
    |
    */

    /**
     * Show the gallery.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGallery(Request $request) {
        if (!config('aldebaran.settings.navigation.gallery')) {
            abort(404);
        }

        $query = Piece::visible($request->user() ?? null)->gallery();

        $data = $request->only(['project_id', 'name', 'tags', 'sort']);
        if (isset($data['project_id']) && $data['project_id'] != 'none') {
            $query->where('project_id', $data['project_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $query->whereRelation('tags.tag', 'id', $tag);
            }
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->orderBy('name');
                    break;
                case 'alpha-reverse':
                    $query->orderBy('name', 'DESC');
                    break;
                case 'project':
                    $query->orderBy('project_id', 'DESC');
                    break;
                case 'newest':
                    $query->sort();
                    break;
                case 'oldest':
                    $query->orderByRaw('ifnull(timestamp, created_at)');
                    break;
            }
        } else {
            $query->sort();
        }

        return view('gallery.gallery', [
            'page'     => TextPage::where('key', 'gallery')->first(),
            'pieces'   => $query->paginate(20)->appends($request->query()),
            'tags'     => Tag::visible()->where('is_active', 1)->pluck('name', 'id'),
            'projects' => ['none' => 'Any Project'] + Project::whereIn('id', Piece::gallery()->pluck('project_id')->toArray())->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Show a project.
     *
     * @param string $name
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getProject($name, Request $request) {
        $project = Project::where('name', str_replace('_', ' ', $name))->first();
        if (!$project || (!Auth::check() && !$project->is_visible)) {
            abort(404);
        }

        $query = Piece::visible($request->user() ?? null)->where('project_id', $project->id);

        $data = $request->only(['project_id', 'name', 'tags', 'sort']);
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $query->whereIn('id', PieceTag::visible()->where('tag_id', $tag)->pluck('piece_id')->toArray());
            }
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->orderBy('name');
                    break;
                case 'alpha-reverse':
                    $query->orderBy('name', 'DESC');
                    break;
                case 'newest':
                    $query->sort();
                    break;
                case 'oldest':
                    $query->orderByRaw('ifnull(timestamp, created_at)');
                    break;
            }
        } else {
            $query->sort();
        }

        return view('gallery.project', [
            'project' => $project,
            'tags'    => Tag::visible()->pluck('name', 'id'),
            'pieces'  => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Show a specific piece.
     *
     * @param int         $id
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPiece(Request $request, $id, $slug = null) {
        $piece = Piece::with('programs')->where('id', $id)->first();
        if (!$piece || (!Auth::check() && !$piece->is_visible)) {
            abort(404);
        }

        if ($request->get('source')) {
            $request->merge([
                'source' => strip_tags($request->get('source')),
            ]);
        }

        $source = $request->get('source');
        // Determine the context in which the piece is being viewed as best able
        switch ($source) {
            case 'gallery':
                $origin = 'gallery';
                break;
            case preg_match('/projects\/[a-zA-z]+/', $source) ? $source : !$source:
                $origin = 'project';
                break;
            case preg_match('/commissions\/types\/[a-zA-Z0-9]+/', $source) ? $source : !$source:
                $origin = 'commissions/type';

                // Locate the relevant commission type now, for convenience
                $matches = [];
                preg_match('/commissions\/types\/([a-zA-Z0-9]+)/', $source, $matches);
                if (isset($matches[1])) {
                    if (is_numeric($matches[1])) {
                        $type = CommissionType::visible()->where('id', $matches[1])->first();
                    } elseif (is_string($matches[1])) {
                        $type = CommissionType::active()->where('key', $matches[1])->where('is_visible', 0)->first();
                    }
                }
                break;
            case preg_match('/commissions\/[a-zA-z]+/', $source) ? $source : !$source:
                $origin = 'commissions/class';
                break;
            default:
                if ($piece->showInGallery) {
                    $origin = 'gallery';
                } else {
                    $origin = 'project';
                }
                break;
        }

        if (config('aldebaran.settings.navigation.piece_previous_next_buttons')) {
            // Determine the piece's nearest neighbors within that context
            $pieces = Piece::visible($request->user() ?? null)->sort();
            switch ($origin) {
                case 'gallery':
                    $pieces->gallery();
                    break;
                case 'project':
                    $pieces->where('project_id', $piece->project_id);
                    break;
                case 'commissions/class':
                    // It's not viable to contextualize from the provided info,
                    // so just don't display any neighbors
                    $pieces = null;
                    break;
                case 'commissions/type':
                    if (isset($type) && $type) {
                        $pieces->whereIn('id', $type->getExamples($request->user() ?? null, true)->pluck('id')->toArray());
                    } else {
                        // Clear pieces so as to avoid misrepresenting
                        // pieces that aren't examples as such
                        $pieces = null;
                    }
                    break;
            }

            if ($pieces) {
                $pieces = $pieces->get();

                // Filter
                $neighbors['previous'] = $pieces->filter(function ($previous) use ($piece) {
                    return $previous->date < $piece->date;
                })->first();
                $neighbors['next'] = $pieces->filter(function ($next) use ($piece) {
                    return $next->date > $piece->date;
                })->last();
            }
        }

        // Then repurpose origin to provide breadcrumbs
        switch ($origin) {
            case 'gallery':
                $origin = ['Gallery' => 'gallery'];
                break;
            case 'project':
                $origin = [$piece->project->name => 'projects/'.$piece->project->slug];
                break;
            case 'commissions/class':
                $matches = [];
                preg_match('/commissions\/([a-zA-z]+)/', $source, $matches);
                if (isset($matches[1])) {
                    // Locate the relevant commission class
                    $class = CommissionClass::active()->where('slug', $matches[1])->first();
                    if ($class) {
                        $origin = [$class->name.' Commissions' => $source];
                    }
                }
                break;
            case 'commissions/type':
                if (isset($type) && $type) {
                    // Include the type's info page and/or gallery if relevant
                    $origin = [
                        $type->category->class->name.' Commissions' => 'commissions/'.$type->category->class->slug,
                    ] + ($type->is_active && !$type->is_visible ? [
                        $type->name => 'commissions/types/'.($type->is_visible ? $type->id : $type->key),
                    ] : []) + ($source == 'commissions/types/'.($type->is_visible ? $type->id : $type->key).'/gallery' ? [
                        'Example Gallery'.($type->is_visible ? ': '.$type->name : '') => 'commissions/types/'.($type->is_visible ? $type->id : $type->key).'/gallery',
                    ] : []);
                }
                break;
        }
        if (!is_array($origin)) {
            // Fall back to project as a failsafe
            $origin = [$piece->project->name => 'projects/'.$piece->project->slug];
        }

        return view('gallery.piece', [
            'piece'     => $piece,
            'origin'    => $origin,
            'neighbors' => $neighbors ?? null,
        ]);
    }

    /**
     * Shows the modal video view.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getVideo($id) {
        $image = PieceImage::where('id', $id)->visible(Auth::user() ?? null)->first();
        if (!$image || !$image->isVideo) {
            abort(404);
        }

        return view('gallery._video_view', [
            'image' => $image,
        ]);
    }
}
