<?php

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\Project;
use App\Models\TextPage;
use App\Services\CommissionManager;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Commission Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing of commission information and submission of commission request forms.
    |
    */

    /**
     * Show commission information.
     *
     * @param string $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getInfo($class, Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::active($request->user() ?? null)->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view('commissions.info', [
            'class'      => $class,
            'page'       => TextPage::where('key', $class->slug.'info')->first(),
            'categories' => CommissionCategory::byClass($class->id)->active()->orderBy('sort', 'DESC')->whereIn('id', CommissionType::visible()->pluck('category_id')->toArray())->get(),
            'count'      => new CommissionType,
        ]);
    }

    /**
     * Show commission ToS.
     *
     * @param string $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTos($class, Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::active($request->user() ?? null)->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view('commissions.text_page', [
            'class' => $class,
            'page'  => TextPage::where('key', $class->slug.'tos')->first(),
        ]);
    }

    /**
     * Show art commission information.
     *
     * @param string $class
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getClassPage($class, $key, Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::active($request->user() ?? null)->where('slug', $class)->first();
        $page = TextPage::where('key', $key)->first();

        if (!$class || !$page) {
            abort(404);
        }

        // Fallback for testing purposes
        if (!is_array($class->data)) {
            $class->data = json_decode($class->data, true);
        }

        if (!isset($class->data['pages'][$page->id])) {
            abort(404);
        }

        return view('commissions.text_page', [
            'class' => $class,
            'page'  => $page,
        ]);
    }

    /**
     * Show commission queue.
     *
     * @param string $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueue($class, Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::active($request->user() ?? null)->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view('commissions.queue', [
            'class'       => $class,
            'commissions' => Commission::class($class->id)->where('status', 'Accepted')->orderBy('created_at', 'ASC')->get(),
        ]);
    }

    /**
     * Show a commission type using its secret key.
     *
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getType($key, Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }
        $type = CommissionType::active()->where('key', $key)->where('is_visible', 0)->first();
        if (!$type || !$type->category->class->is_active) {
            abort(404);
        }

        return view('commissions.type', [
            'type' => $type,
        ]);
    }

    /**
     * Show the gallery of examples for a given commission type.
     *
     * @param int|string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTypeGallery($key, Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }

        // Find the commission type, either via ID or, failing that,
        // via its key.
        if (is_numeric($key)) {
            $type = CommissionType::visible()->where('id', $key)->first();
        }
        if (!isset($type)) {
            $type = CommissionType::active()->where('key', $key)->first();
            $source = 'key';
        }
        if (!$type || (!$request->user() && !$type->category->class->is_active) || !$type->show_examples) {
            abort(404);
        }

        // Fetch visible examples
        $query = Piece::visible($request->user() ?? null)->whereIn('id', $type->getExamples($request->user() ?? null, true)->pluck('id')->toArray());

        // Perform any filtering/sorting
        $data = $request->only(['project_id', 'name', 'sort']);
        if (isset($data['project_id']) && $data['project_id'] != 'none') {
            $query->where('project_id', $data['project_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
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

        return view('commissions.type_gallery', [
            'type'     => $type,
            'pieces'   => $query->paginate(20)->appends($request->query()),
            'projects' => ['none' => 'Any Project'] + Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'source'   => $source ?? null,
        ]);
    }

    /**
     * Show the new commission request form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewCommission(Request $request)
    {
        if (!config('aldebaran.settings.commissions.enabled')) {
            abort(404);
        }

        // Retrive type ID and if relevant key from request
        $data = $request->only(['type', 'key']);
        // and then check for and retreive type,
        if (!isset($data['type'])) {
            abort(404);
        }
        $type = CommissionType::active()->find($data['type']);
        if (!$type) {
            abort(404);
        }
        // check that the type is active and commissions of the global type are open,
        if (!Settings::get($type->category->class->slug.'_comms_open') || !$type->category->class->is_active) {
            abort(404);
        }
        // and, if relevant, that the key is good.
        if (!$type->is_visible && (!isset($data['key']) || $type->key != $data['key'])) {
            abort(404);
        }

        return view('commissions.new', [
            'page' => TextPage::where('key', 'new_commission')->first(),
            'type' => $type,
        ]);
    }

    /**
     * Submits a new commission request.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewCommission(Request $request, CommissionManager $service, $id = null)
    {
        $type = CommissionType::find($request->get('type'));

        // Form an array of possible answers based on configured fields,
        // Set any un-set toggles (since Laravel does not pass anything on for them),
        // and collect any custom validation rules for the configured fields
        $answerArray = [];
        $validationRules = Commission::$createRules;
        foreach ($type->formFields as $key=>$field) {
            $answerArray[$key] = null;
            if (isset($field['rules'])) {
                $validationRules[$key] = $field['rules'];
            }
            if ($field['type'] == 'checkbox' && !isset($request[$key])) {
                $request[$key] = 0;
            }
        }

        // If the app is running in a prod environment,
        // validate recaptcha response as well
        if (config('app.env') == 'production') {
            $validationRules['g-recaptcha-response'] = 'required|recaptchav3:submit,0.5';
        }

        $request->validate($validationRules);

        $data = $request->only([
            'name', 'email', 'contact', 'paypal', 'type', 'key', 'additional_information',
            ] + $answerArray);
        $data['ip'] = $request->ip();

        if (!$id && $commission = $service->createCommission($data)) {
            flash('Commission request submitted successfully.')->success();

            return redirect()->to('commissions/view/'.$commission->commission_key);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Show the commission status page.
     *
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getViewCommission($key)
    {
        $commission = Commission::where('commission_key', $key)->first();
        if (!$commission) {
            abort(404);
        }

        return view('commissions.view_commission', [
            'commission' => $commission,
        ]);
    }
}
