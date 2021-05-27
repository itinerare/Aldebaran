<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use View;

use App\Models\TextPage;
use App\Models\Changelog;
use App\Models\Gallery\Project;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Creates a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        View::share('visibleProjects', Project::visible()->orderBy('sort', 'DESC')->get());
    }

    /**
     * Show the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $page = TextPage::where('key', 'index')->first();
        if(!$page) abort(404);
        return view('index', [
            'page' => $page
        ]);
    }

    /**
     * Show the about page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAbout()
    {
        $page = TextPage::where('key', 'about')->first();
        if(!$page) abort(404);
        return view('text_page', [
            'page' => $page
        ]);
    }

    /**
     * Show the privacypolicy page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPrivacyPolicy()
    {
        $page = TextPage::where('key', 'privacy')->first();
        if(!$page) abort(404);
        return view('text_page', [
            'page' => $page
        ]);
    }

    /**
     * Show the changelog page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getChangelog()
    {
        return view('changelog', [
            'changelogs' => Changelog::visible()->orderBy('created_at', 'DESC')->paginate(20)
        ]);
    }

    /**
     * Show the placeholder page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPlaceholder()
    {
        return view('placeholder');
    }
}
