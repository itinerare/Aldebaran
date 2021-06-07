<?php

namespace App\Http\Controllers;

use Auth;
use Config;
use Settings;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionType;
use App\Models\Commission\Commission;

use App\Models\Gallery\Project;
use App\Models\Gallery\Piece;
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
     * @param  string    $type
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getInfo($type)
    {
        if(!isset(Config::get('itinerare.comm_types')[$type])) abort(404);

        return view('commissions.info',
        [
            'type' => $type,
            'page' => TextPage::where('key', $type.'info')->first(),
            'categories' => CommissionCategory::type($type)->active()->orderBy('sort', 'DESC')->whereIn('id', CommissionType::visible()->pluck('category_id')->toArray())->get(),
            'count' => new CommissionType,
        ]);
    }

    /**
     * Show commission ToS.
     *
     * @param  string    $type
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTos($type)
    {
        if(!isset(Config::get('itinerare.comm_types')[$type])) abort(404);

        return view('commissions.tos',
        [
            'type' => $type,
            'page' => TextPage::where('key', $type.'tos')->first()
        ]);
    }

    /**
     * Show commission queue.
     *
     * @param  string    $type
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueue($type)
    {
        if(!isset(Config::get('itinerare.comm_types')[$type])) abort(404);

        return view('commissions.queue',
        [
            'type' => $type,
            'commissions' => Commission::type($type)->where('status', 'Accepted')->orderBy('created_at', 'ASC')->get()
        ]);
    }

    /**
     * Show a commission type using its secret key.
     *
     * @param string      $key
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getType($key)
    {
        $type = CommissionType::active()->where('key', $key)->first();
        if(!$type) abort(404);

        return view('commissions.type',
        [
            'type' => $type
        ]);
    }

    /**
     * Show the gallery of examples for a given commission type.
     *
     * @param  int|string $key
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTypeGallery($key, Request $request)
    {
        // Find the commission type, either via ID or, failing that,
        // via its key.
        if(is_numeric($key)) $type = CommissionType::visible()->where('id', $key)->first();
        if(!isset($type)) {
            $type = CommissionType::active()->where('key', $key)->first();
            $source = 'key';
        }
        if(!$type) abort(404);

        if(!$type->data['show_examples']) abort(404);

        // Fetch visible examples
        $query = Piece::visible(Auth::check() ? Auth::user() : null)->whereIn('id', $type->getExamples(Auth::check() ? Auth::user() : null, true)->pluck('id')->toArray());

        // Perform any filtering/sorting
        $data = $request->only(['project_id', 'name', 'sort']);
        if(isset($data['project_id']) && $data['project_id'] != 'none')
            $query->where('project_id', $data['project_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        if(isset($data['sort']))
        {
            switch($data['sort']) {
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
        }
        else $query->sort();

        return view('commissions.type_gallery', [
            'type' => $type,
            'pieces' => $query->paginate(20)->appends($request->query()),
            'projects' => ['none' => 'Any Project'] + Project::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'source' => isset($source) ? $source : null
        ]);
    }

    /**
     * Show the new commission request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewCommission(Request $request)
    {
        // Retrive type ID and if relevant key from request
        $data = $request->only(['type', 'key']);
        // and then check for and retreive type,
        if(!isset($data['type'])) abort(404);
        $type = CommissionType::active()->find($data['type']);
        if(!$type) abort(404);
        // check that the type is active and commissions of the global type are open,
        if(!Settings::get($type->category->type.'_comms_open')) abort(404);
        // and, if relevant, that the key is good.
        if(!$type->is_visible && (!isset($data['key']) || $type->key != $data['key'])) abort(404);

        return view('commissions.new',
        [
            'type' => $type
        ]);
    }

    /**
     * Submits a new commission request.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  App\Services\CommissionManager  $service
     * @param  int|null                        $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewCommission(Request $request, CommissionManager $service, $id = null)
    {
        $type = CommissionType::find($request->get('type'));

        // Form an array of possible answers based on configured fields,
        // Set any un-set toggles (since Laravel does not pass anything on for them),
        // and collect any custom validation rules for the configured fields
        $answerArray = []; $validationRules = Commission::$createRules;
        foreach([$type->category->name.'_'.$type->name, $type->category->name, 'basic'] as $section)
            if(Config::get('itinerare.comm_types.'.$type->category->type.'.forms.'.$section) != null) {
                foreach(Config::get('itinerare.comm_types.'.$type->category->type.'.forms.'.$section) as $key=>$field) {
                    if($key != 'includes') {
                        $answerArray[$key] = null;
                        if(isset($field['validation_rules'])) $validationRules[$key] = $field['validation_rules'];
                        if($field['type'] == 'checkbox' && !isset($request[$key])) $request[$key] = 0;
                    }
                    elseif($key == 'includes')
                        foreach(Config::get('itinerare.comm_types.'.$type.'.forms.'.$include) as $key=>$field) {
                            $answerArray[$key] = null;
                            if(isset($field['validation_rules'])) $validationRules[$key] = $field['validation_rules'];
                            if($field['type'] == 'checkbox' && !isset($request[$key])) $request[$key] = 0;
                        }
                }
            break;
            }

        $request->validate($validationRules);

        $data = $request->only([
            'name', 'email', 'contact', 'paypal', 'type', 'key',
        ] + $answerArray);
        $data['ip'] = $request->ip();

        if (!$id && $commission = $service->createCommission($data)) {
            flash('Commission request submitted successfully.')->success();
            return redirect()->to('commissions/view/'.$commission->key);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Show the commission status page.
     *
     * @param  string             $key
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getViewCommission($key)
    {
        $commission = Commission::where('key', $key)->first();
        if(!$commission) abort(404);

        return view('commissions.view_commission',
        [
            'commission' => $commission
        ]);
    }

    /******************************************************************************
        ART COMMS
    *******************************************************************************/

    /**
     * Show art commission information.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWillWont()
    {
        return view('commissions.willwont',
        [
            'page' => TextPage::where('key', 'willwont')->first()
        ]);
    }

}
