<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use App\Models\Commission\CommissionClass;
use App\Models\Gallery\Project;
use App\Models\TextPage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        View::share('visibleProjects', Project::visible()->orderBy('sort', 'DESC')->get());
        view()->composer('*', function ($view) {
            $commissionClasses = CommissionClass::active(Auth::check() ? Auth::user() : null)->orderBy('sort', 'DESC')->get();

            $view->with('commissionClasses', $commissionClasses);
        });
    }

    /**
     * Show the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $page = TextPage::where('key', 'index')->first();
        if (!$page) {
            abort(404);
        }

        return view('index', [
            'page' => $page,
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
        if (!$page) {
            abort(404);
        }

        return view('text_page', [
            'page' => $page,
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
        if (!$page) {
            abort(404);
        }

        return view('text_page', [
            'page' => $page,
        ]);
    }

    /**
     * Show the changelog page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getChangelog(Request $request)
    {
        return view('changelog', [
            'changelogs' => Changelog::visible()->orderBy('created_at', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Show the feed index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeeds()
    {
        return view('feed_index');
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
