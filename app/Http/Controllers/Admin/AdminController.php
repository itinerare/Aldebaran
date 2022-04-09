<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Commission\Commission;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin Controller
    |--------------------------------------------------------------------------
    |
    | Handles general admin requests.
    |
    */

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $pendingCount = [];
        $acceptedCount = [];
        foreach ($this->commissionClasses as $class) {
            $pendingCount[$class->id] = Commission::where('status', 'Pending')->class($class->id)->count();
            $acceptedCount[$class->id] = Commission::where('status', 'Accepted')->class($class->id)->count();
        }

        return view('admin.index', [
            'pendingCount'  => $pendingCount,
            'acceptedCount' => $acceptedCount,
        ]);
    }

    /******************************************************************************
        SITE SETTINGS
    *******************************************************************************/

    /**
     * Shows the settings index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSettings()
    {
        return view('admin.settings', [
            'settings' => DB::table('site_settings')->orderBy('key')->get(),
        ]);
    }

    /**
     * Edits a setting.
     *
     * @param string $key
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditSetting(Request $request, $key)
    {
        if (!$request->get('value')) {
            $value = 0;
        }
        if (DB::table('site_settings')->where('key', $key)->update(['value' => isset($value) ? $value : $request->get('value')])) {
            flash('Setting updated successfully.')->success();
        } else {
            flash('Invalid setting selected.')->success();
        }

        return redirect()->back();
    }

    /******************************************************************************
        SITE IMAGES
    *******************************************************************************/

    /**
     * Shows the site images index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSiteImages()
    {
        return view('admin.images', [
            'images' => config('aldebaran.image_files'),
        ]);
    }

    /**
     * Uploads a site image file.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUploadImage(Request $request, FileService $service)
    {
        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');
        $key = $request->get('key');
        $filename = config('aldebaran.image_files.'.$key)['filename'];

        if ($service->uploadFile($file, null, $filename, false)) {
            flash('Image uploaded successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Uploads a custom site CSS file.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUploadCss(Request $request, FileService $service)
    {
        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');

        if ($service->uploadCss($file)) {
            flash('File uploaded successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
