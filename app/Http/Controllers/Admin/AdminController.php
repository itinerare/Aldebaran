<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

use Config;
use Settings;
use DB;
use Auth;

use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

use App\Services\UserService;
use App\Services\FileManager;
use App\Models\Commission\Commission;

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
        return view('admin.index', [
            'pendingComms' => Commission::where('status', 'Pending'),
            'acceptedComms' => Commission::where('status', 'Accepted'),
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
            'settings' => DB::table('site_settings')->orderBy('key')->get()
        ]);
    }

    /**
     * Edits a setting.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  string                         $key
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditSetting(Request $request, $key)
    {
        if(!$request->get('value')) $value = 0;
        if(DB::table('site_settings')->where('key', $key)->update(['value' => isset($value) ? $value : $request->get('value')])) {
            flash('Setting updated successfully.')->success();
        }
        else {
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
            'images' => Config::get('itinerare.image_files')
        ]);
    }

    /**
     * Uploads a site image file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\FileManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUploadImage(Request $request, FileManager $service)
    {
        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');
        $key = $request->get('key');
        $filename = Config::get('itinerare.image_files.'.$key)['filename'];

        if($service->uploadFile($file, null, $filename, false)) {
            flash('Image uploaded successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Uploads a custom site CSS file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\FileManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUploadCss(Request $request, FileManager $service)
    {
        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');

        if($service->uploadCss($file)) {
            flash('File uploaded successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
