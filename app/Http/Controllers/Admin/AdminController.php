<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionQuote;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {
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
    public function getIndex() {
        if (config('aldebaran.commissions.enabled')) {
            $pendingCount = [];
            $acceptedCount = [];
            foreach ($this->commissionClasses as $class) {
                $pendingCount['commissions'][$class->id] = Commission::where('status', 'Pending')->class($class->id)->count();
                $acceptedCount['commissions'][$class->id] = Commission::where('status', 'Accepted')->class($class->id)->count();
                $pendingCount['quotes'][$class->id] = CommissionQuote::where('status', 'Pending')->class($class->id)->count();
                $acceptedCount['quotes'][$class->id] = CommissionQuote::where('status', 'Accepted')->class($class->id)->count();
            }
        }

        return view('admin.index', [
            'pendingCount'  => $pendingCount ?? 0,
            'acceptedCount' => $acceptedCount ?? 0,
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
    public function getSettings() {
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
    public function postEditSetting(Request $request, $key) {
        $fieldname = $key.'_value';
        if (!$request->get($fieldname)) {
            $value = 0;
        }
        if (DB::table('site_settings')->where('key', $key)->exists()) {
            DB::table('site_settings')->where('key', $key)->update(['value' => $value ?? $request->get($fieldname)]);
            flash('Setting updated successfully.')->success();
        } else {
            flash('Invalid setting selected.')->error();
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
    public function getSiteImages() {
        return view('admin.images', [
            'images' => config('aldebaran.image_files'),
        ]);
    }

    /**
     * Uploads a site image file.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUploadImage(Request $request, FileService $service) {
        $key = $request->get('key');
        $fieldname = $key.'_file';
        $request->validate([$fieldname => 'required|file']);
        $file = $request->file($fieldname);
        $filename = config('aldebaran.image_files.'.$key)['filename'].'.'.config('aldebaran.settings.image_formats.site_images', 'png');

        if ($service->uploadFile($file, null, $filename, false)) {
            flash('Image uploaded successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Uploads a custom site CSS file.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUploadCss(Request $request, FileService $service) {
        $request->validate(['css_file' => 'required|file']);
        $file = $request->file('css_file');

        if ($service->uploadCss($file)) {
            flash('File uploaded successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }
}
