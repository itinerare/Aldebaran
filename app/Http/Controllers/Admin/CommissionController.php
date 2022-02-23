<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Services\CommissionManager;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Commission Controller
    |--------------------------------------------------------------------------
    |
    | Handles management of commissions queues.
    |
    */

    /**
     * Shows the commission index page.
     *
     * @param string $status
     * @param mixed  $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCommissionIndex(Request $request, $class, $status = null)
    {
        $class = CommissionClass::where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        $commissions = Commission::class($class->id)->where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['commission_type', 'sort']);
        if (isset($data['commission_type']) && $data['commission_type'] != 'none') {
            $commissions->where('commission_type', $data['commission_type']);
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $commissions->orderBy('created_at', 'DESC');
                    break;
                case 'oldest':
                    $commissions->orderBy('created_at', 'ASC');
                    break;
            }
        } else {
            $commissions->orderBy('created_at');
        }

        return view('admin.queues.index', [
            'commissions' => $commissions->paginate(30)->appends($request->query()),
            'types'       => ['none' => 'Any Type'] + CommissionType::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'class'       => $class,
            'count'       => new CommissionType,
        ]);
    }

    /**
     * Show the new commission request form.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewCommission($id)
    {
        $type = CommissionType::where('id', $id)->first();
        if (!$type) {
            abort(404);
        }

        $commissioners = Commissioner::where('is_banned', 0)->get()->pluck('fullName', 'id')->sort()->toArray();

        return view('admin.queues.new', [
            'type'          => $type,
            'commissioners' => $commissioners,
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
        $type = CommissionType::where('id', $request->get('type'))->first();
        if (!$type) {
            abort(404);
        }

        $answerArray = [];
        $validationRules = Commission::$manualCreateRules;
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
            'commissioner_id', 'name', 'email', 'contact', 'paypal', 'type', 'additional_information',
        ] + $answerArray);
        if (!$id && $commission = $service->createCommission($data, true)) {
            flash('Commission submitted successfully.')->success();

            return redirect()->to('admin/commissions/edit/'.$commission->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the commission detail page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCommission($id)
    {
        $commission = Commission::find($id);
        if (!$commission) {
            abort(404);
        }

        return view('admin.queues.commission', [
            'commission' => $commission,
        ] + ($commission->status == 'Pending' || $commission->status == 'Accepted' ? [
            'pieces' => Piece::sort()->pluck('name', 'id')->toArray(),
        ] : []));
    }

    /**
     * Acts on a commission.
     *
     * @param int    $id
     * @param string $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCommission($id, $action, Request $request, CommissionManager $service)
    {
        switch ($action) {
            default:
                flash('Invalid action selected.')->error();
                break;
            case 'accept':
                return $this->postAcceptCommission($id, $request, $service);
                break;
            case 'update':
                return $this->postUpdateCommission($id, $request, $service);
                break;
            case 'complete':
                return $this->postCompleteCommission($id, $request, $service);
                break;
            case 'decline':
                return $this->postDeclineCommission($id, $request, $service);
                break;
            case 'ban':
                return $this->postBanCommissioner($id, $request, $service);
                break;
        }

        return redirect()->back();
    }

    /**
     * Show the ledger.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLedger(Request $request)
    {
        $yearCommissions = Commission::whereIn('status', ['Accepted', 'Complete'])->whereNotNull('cost_data')->orderBy('created_at', 'DESC')->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y');
        });

        $yearPayments = CommissionPayment::orderBy('created_at', 'DESC')->get()->filter(function ($payment) {
            if ($payment->is_paid) {
                return 1;
            } elseif ($payment->commission->status == 'Accepted' || $payment->commission->status == 'Complete') {
                return 1;
            }

            return 0;
        })->groupBy(function ($date) {
            if (isset($date->paid_at)) {
                return Carbon::parse($date->paid_at)->format('Y');
            }

            return Carbon::now()->format('Y');
        });

        $groupedPayments = $yearPayments->map(function ($year) {
            return $year->groupBy(function ($payment) {
                if (isset($payment->paid_at)) {
                    return Carbon::parse($payment->paid_at)->format('F Y');
                }

                return Carbon::parse($payment->created_at)->format('F Y');
            });
        })->sort();

        return view('admin.queues.ledger', [
            'years'           => $groupedPayments->paginate(1)->appends($request->query()),
            'yearPayments'    => $yearPayments,
            'yearCommissions' => $yearCommissions,
            'year'            => $groupedPayments->keys()->skip(($request->get('page') ? $request->get('page') : 1) - 1)->first(),
        ]);
    }

    /**
     * Accepts a commission.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAcceptCommission($id, Request $request, CommissionManager $service)
    {
        if ($service->acceptCommission($id, $request->only(['comments']), $request->user())) {
            flash('Commission accepted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Updates a commission.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postUpdateCommission($id, Request $request, CommissionManager $service)
    {
        $request->validate(Commission::$updateRules);
        $data = $request->only(['pieces', 'paid_status', 'progress', 'comments', 'cost', 'tip', 'is_paid', 'is_intl', 'paid_at']);
        if ($service->updateCommission($id, $data, $request->user())) {
            flash('Commission updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Marks a commission complete.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postCompleteCommission($id, Request $request, CommissionManager $service)
    {
        if ($service->completeCommission($id, $request->only(['comments']), $request->user())) {
            flash('Commission marked complete successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Declines a commission.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postDeclineCommission($id, Request $request, CommissionManager $service)
    {
        if ($service->declineCommission($id, $request->only(['comments']), $request->user())) {
            flash('Commission declined successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Accepts a commission.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postBanCommissioner($id, Request $request, CommissionManager $service)
    {
        if ($service->banCommissioner($id, $request->only(['comments']), $request->user())) {
            flash('Commissioner banned successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
