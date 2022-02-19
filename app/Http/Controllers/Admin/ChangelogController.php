<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Changelog;
use App\Services\ChangelogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangelogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Changelog Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of changelog entries.
    |
    */

    /**
     * Shows the changelog index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getChangelogIndex(Request $request)
    {
        return view('admin.changelog.index', [
            'logs' => Changelog::orderBy('created_at', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create changelog page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLog()
    {
        return view('admin.changelog.create_edit_log', [
            'log' => new Changelog,
        ]);
    }

    /**
     * Shows the edit changelog page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLog($id)
    {
        $log = Changelog::find($id);
        if (!$log) {
            abort(404);
        }

        return view('admin.changelog.create_edit_log', [
            'log' => $log,
        ]);
    }

    /**
     * Creates or edits a changelog.
     *
     * @param App\Services\ChangelogService $service
     * @param int|null                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLog(Request $request, ChangelogService $service, $id = null)
    {
        $id ? $request->validate(Changelog::$updateRules) : $request->validate(Changelog::$createRules);
        $data = $request->only([
            'name', 'text', 'is_visible',
        ]);
        if ($id && $service->updateLog(Changelog::find($id), $data, Auth::user())) {
            flash('Entry updated successfully.')->success();
        } elseif (!$id && $log = $service->createLog($data, Auth::user())) {
            flash('Entry created successfully.')->success();

            return redirect()->to('admin/changelog/edit/'.$log->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the changelog deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLog($id)
    {
        $log = Changelog::find($id);

        return view('admin.changelog._delete_log', [
            'log' => $log,
        ]);
    }

    /**
     * Deletes a changelog.
     *
     * @param App\Services\PageService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLog(Request $request, ChangelogService $service, $id)
    {
        if ($id && $service->deleteLog(Changelog::find($id))) {
            flash('Entry deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/changelog');
    }
}
