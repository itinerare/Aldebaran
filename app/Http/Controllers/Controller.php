<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use View;
use Illuminate\Http\Request;

use App\Models\TextPage;
use App\Models\Changelog;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
     * @param  \Illuminate\Http\Request        $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getChangelog(Request $request)
    {
        return view('changelog', [
            'changelogs' => Changelog::visible()->orderBy('created_at', 'DESC')->paginate(20)->appends($request->query())
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
