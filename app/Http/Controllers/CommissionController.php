<?php

namespace App\Http\Controllers;

use App\Models\Commission\Commission;
use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\Project;
use App\Models\TextPage;
use App\Services\CommissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Settings;

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
    public function getInfo($class)
    {
        if (!Settings::get('commissions_on')) {
            abort(404);
        }
        $class = CommissionClass::active()->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view(
            'commissions.info',
            [
            'class'      => $class,
            'page'       => TextPage::where('key', $class->slug.'info')->first(),
            'categories' => CommissionCategory::byClass($class->id)->active()->orderBy('sort', 'DESC')->whereIn('id', CommissionType::visible()->pluck('category_id')->toArray())->get(),
            'count'      => new CommissionType,
        ]
        );
    }

    /**
     * Show commission ToS.
     *
     * @param string $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTos($class)
    {
        if (!Settings::get('commissions_on')) {
            abort(404);
        }
        $class = CommissionClass::active()->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view(
            'commissions.text_page',
            [
            'class' => $class,
            'page'  => TextPage::where('key', $class->slug.'tos')->first(),
        ]
        );
    }

    /**
     * Show art commission information.
     *
     * @param string $class
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getClassPage($class, $key)
    {
        if (!Settings::get('commissions_on')) {
            abort(404);
        }
        $class = CommissionClass::active()->where('slug', $class)->first();
        $page = TextPage::where('key', $key)->first();

        if (!$class || !$page || !isset($class->data['pages'][$page->id])) {
            abort(404);
        }

        return view(
            'commissions.text_page',
            [
            'class' => $class,
            'page'  => $page,
        ]
        );
    }

    /**
     * Show commission queue.
     *
     * @param string $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueue($class)
    {
        if (!Settings::get('commissions_on')) {
            abort(404);
        }
        $class = CommissionClass::active()->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view(
            'commissions.queue',
            [
            'class'       => $class,
            'commissions' => Commission::class($class->id)->where('status', 'Accepted')->orderBy('created_at', 'ASC')->get(),
        ]
        );
    }

    /**
     * Show a commission type using its secret key.
     *
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getType($key)
    {
        if (!Settings::get('commissions_on')) {
            abort(404);
        }
        $type = CommissionType::active()->where('key', $key)->first();
        if (!$type) {
            abort(404);
        }

        return view(
            'commissions.type',
            [
            'type' => $type,
        ]
        );
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
        if (!Settings::get('commissions_on')) {
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
        if (!$type) {
            abort(404);
        }

        if (!$type->data['show_examples']) {
            abort(404);
        }

        // Fetch visible examples
        $query = Piece::visible(Auth::check() ? Auth::user() : null)->whereIn('id', $type->getExamples(Auth::check() ? Auth::user() : null, true)->pluck('id')->toArray());

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
            'source'   => isset($source) ? $source : null,
        ]);
    }

    /**
     * Show the new commission request form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewCommission(Request $request)
    {
        if (!Settings::get('commissions_on')) {
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
        if (!Settings::get($type->category->class->slug.'_comms_open')) {
            abort(404);
        }
        // and, if relevant, that the key is good.
        if (!$type->is_visible && (!isset($data['key']) || $type->key != $data['key'])) {
            abort(404);
        }

        return view(
            'commissions.new',
            [
            'page' => TextPage::where('key', 'new_commission')->first(),
            'type' => $type,
        ]
        );
    }

    /**
     * Submits a new commission request.
     *
     * @param App\Services\CommissionManager $service
     * @param int|null                       $id
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
                flash($error)->error();
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
        if (!Settings::get('commissions_on')) {
            abort(404);
        }
        $commission = Commission::where('commission_key', $key)->first();
        if (!$commission) {
            abort(404);
        }

        return view(
            'commissions.view_commission',
            [
            'commission' => $commission,
        ]
        );
    }
}
