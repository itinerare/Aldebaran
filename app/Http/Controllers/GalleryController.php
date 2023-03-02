<?php

namespace App\Http\Controllers;

use App\Models\Gallery\Piece;
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
        $piece = Piece::find($id);
        if (!$piece || (!Auth::check() && !$piece->is_visible)) {
            abort(404);
        }

        // Determine the context in which the piece is being viewed as best able
        switch ($request->get('source')) {
            case 'gallery':
                $origin = 'gallery';
                break;
            case 'projects/'.$piece->project->slug:
                $origin = 'project';
                break;
            default:
                if ($piece->showInGallery) {
                    $origin = 'gallery';
                } else {
                    $origin = 'project';
                }
                break;
        }

        if(config('aldebaran.settings.navigation.piece_previous_next_buttons')) {
            // Determine the piece's nearest neighbors within that context
            $pieces = Piece::visible(Auth::check() ? Auth::user() : null)->sort();
            if ($origin == 'gallery') {
                $pieces->gallery();
            } else {
                $pieces->where('project_id', $piece->project_id);
            }
            $pieces = $pieces->get();

            // Filter
            $neighbors['previous'] = $pieces->filter(function ($previous) use ($piece) {
                return $previous->date < $piece->date;
            })->first();
            $neighbors['next'] = $pieces->filter(function ($next) use ($piece) {
                return $next->date > $piece->date;
            })->last();
        }

        return view('gallery.piece', [
            'piece'     => $piece,
            'origin'    => $origin,
            'neighbors' => $neighbors ?? null,
        ]);
    }
}
