<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TextPage;
use App\Services\PageService;
use Auth;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Text Page Controller
    |--------------------------------------------------------------------------
    |
    | Handles editing of text pages.
    |
    */

    /**
     * Shows the text page index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPagesIndex(Request $request)
    {
        return view('admin.pages.index', [
            'pages' => TextPage::orderBy('key')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the edit text page page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPage($id)
    {
        $page = TextPage::find($id);
        if (!$page) {
            abort(404);
        }

        return view('admin.pages.edit_page', [
            'page' => $page,
        ]);
    }

    /**
     * Edits a text page.
     *
     * @param App\Services\PageService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditPage(Request $request, PageService $service, $id = null)
    {
        $request->validate(TextPage::$updateRules);
        $data = $request->only(['text']);

        if ($service->updatePage(TextPage::find($id), $data, Auth::user())) {
            flash('Page updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
