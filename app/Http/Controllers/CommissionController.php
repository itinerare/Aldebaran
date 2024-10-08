<?php

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionQuote;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\Project;
use App\Models\TextPage;
use App\Services\CommissionManager;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class CommissionController extends Controller {
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
    public function getInfo($class, Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::active($request->user() ?? null)->where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        return view('commissions.info', [
            'class'      => $class,
            'page'       => TextPage::where('key', $class->slug.'info')->first(),
            'categories' => CommissionCategory::byClass($class->id)->active()->orderBy('sort', 'DESC')->whereRelation('types', 'is_visible', true)->get(),
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
    public function getTos($class, Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
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
    public function getClassPage($class, $key, Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
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
    public function getQueue($class, Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
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
    public function getType($key, Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
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
    public function getTypeGallery($key, Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        // Find the commission type, either via ID or, failing that,
        // via its key.
        if (is_numeric($key)) {
            $type = CommissionType::visible()->where('id', $key)->first();
        } elseif (is_string($key)) {
            $type = CommissionType::where('is_visible', 0)->active()->where('key', $key)->first();
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
    public function getNewCommission(Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        // Retrive type ID and if relevant key from request
        $data = $request->only(['type', 'key']);
        // and then check for and retreive type,
        if (!isset($data['type'])) {
            abort(404);
        }
        $type = CommissionType::active()->where('id', $data['type'])->first();
        if (!$type) {
            abort(404);
        }
        // check that the class is active and commissions of the global type are open,
        if (!Settings::get($type->category->class->slug.'_comms_open') || !$type->category->class->is_active) {
            abort(404);
        }
        // and, if relevant, that the key is good.
        if (!$type->is_visible && (!isset($data['key']) || $type->key != $data['key'])) {
            abort(404);
        }

        return view('commissions.new', [
            'page'       => TextPage::where('key', 'new_commission')->first(),
            'type'       => $type,
            'commission' => new Commission,
        ]);
    }

    /**
     * Submits a new commission request.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewCommission(Request $request, CommissionManager $service, $id = null) {
        $type = CommissionType::find($request->get('type'));

        // Form an array of possible answers based on configured fields,
        // Set any un-set toggles (since Laravel does not pass anything on for them),
        // and collect any custom validation rules for the configured fields
        $answerArray = [];
        $validationRules = Commission::$createRules;
        foreach ($type->formFields as $key=> $field) {
            $answerArray[$key] = null;
            if (isset($field['rules'])) {
                $validationRules[$key] = $field['rules'];
            }
            if ($field['type'] == 'checkbox' && !isset($request[$key])) {
                $request[$key] = 0;
            }
        }

        // If the type requires a quote, include this in validation
        if ($type->quote_required) {
            $validationRules['quote_key'] = 'required';
        }

        // If the app is running in a prod environment,
        // validate recaptcha response as well
        if (config('app.env') == 'production' && config('aldebaran.settings.captcha')) {
            $validationRules['g-recaptcha-response'] = 'required|recaptchav3:submit,0.5';
        }

        $request->validate($validationRules);

        $data = $request->only([
            'name', 'email', 'receive_notifications', 'contact',
            'payment_email', 'payment_processor',
            'type', 'key', 'additional_information', 'quote_key',
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
    public function getViewCommission($key) {
        $commission = Commission::where('commission_key', $key)->first();
        if (!$commission) {
            abort(404);
        }

        return view('commissions.view_commission', [
            'commission' => $commission,
        ]);
    }

    /**
     * Show the full size for a commission image.
     *
     * @param string $key
     * @param int    $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getViewCommissionImage($key, $id) {
        $commission = Commission::where('commission_key', $key)->first();
        if (!$commission) {
            abort(404);
        }

        $image = PieceImage::where('id', $id)->first();
        if (!$image) {
            abort(404);
        }

        if (config('aldebaran.settings.image_formats.full') && config('aldebaran.settings.image_formats.commission_full') && !$image->isMultimedia) {
            $file = Image::make($image->imagePath.'/'.$image->fullsizeFileName);

            return $file->response(config('aldebaran.settings.image_formats.commission_full'));
        }

        return redirect()->to($image->fullsizeUrl);
    }

    /**
     * Show the new quote request form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewQuote(Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        // Retrive type ID and if relevant key from request
        $data = $request->only(['type']);
        // and then check for and retreive type,
        if (!isset($data['type'])) {
            abort(404);
        }
        // Unlike for full commissions, for quotes it only matters that they're open
        $type = CommissionType::where('id', $data['type'])->where('quotes_open', 1)->first();
        if (!$type) {
            abort(404);
        }
        // Check the class is active
        if (!$type->category->class->is_active) {
            abort(404);
        }

        return view('commissions.new_quote', [
            'page'       => TextPage::where('key', 'new_quote')->first(),
            'type'       => $type,
            'commission' => new CommissionQuote,
        ]);
    }

    /**
     * Submits a new quote request.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewQuote(Request $request, CommissionManager $service, $id = null) {
        $type = CommissionType::find($request->get('type'));

        $request->validate(Commission::$createRules + (config('app.env') == 'production' && config('aldebaran.settings.captcha') ? [
            // If the app is running in a prod environment,
            // validate recaptcha response as well
            'g-recaptcha-response' => 'required|recaptchav3:submit,0.5',
        ] : []));

        $data = $request->only([
            'name', 'email', 'receive_notifications', 'contact',
            'commission_type_id', 'subject', 'description', 'amount',
        ]);
        $data['ip'] = $request->ip();

        if (!$id && $quote = $service->createQuote($data)) {
            flash('Quote request submitted successfully.')->success();

            return redirect()->to('commissions/quotes/view/'.$quote->quote_key);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Show the quote status page.
     *
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getViewQuote($key) {
        $quote = CommissionQuote::where('quote_key', $key)->first();
        if (!$quote) {
            abort(404);
        }

        return view('commissions.view_quote', [
            'quote' => $quote,
        ]);
    }
}
