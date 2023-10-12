<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionQuote;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Services\CommissionManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class CommissionController extends Controller {
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
    public function getCommissionIndex(Request $request, $class, $status = null) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $class = CommissionClass::where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        $commissions = Commission::with('commissioner')->class($class->id)->where('status', $status ? ucfirst($status) : 'Pending');
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
    public function getNewCommission($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $type = CommissionType::where('id', $id)->first();
        if (!$type) {
            abort(404);
        }

        $commissioners = Commissioner::valid()->get()->pluck('fullName', 'id')->sort()->toArray();

        return view('admin.queues.new', [
            'type'          => $type,
            'commissioners' => $commissioners,
            'commission'    => new Commission,
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
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $type = CommissionType::where('id', $request->get('type'))->first();
        if (!$type) {
            abort(404);
        }

        $answerArray = [];
        $validationRules = Commission::$manualCreateRules;
        foreach ($type->formFields as $key=> $field) {
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
            'commissioner_id', 'name', 'email', 'contact',
            'payment_email', 'payment_processor',
            'type', 'additional_information',
        ] + $answerArray);
        if (!$id && $commission = $service->createCommission($data, true)) {
            flash('Commission submitted successfully.')->success();

            return redirect()->to('admin/commissions/edit/'.$commission->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
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
    public function getCommission($id) {
        $commission = Commission::find($id);
        if (!$commission) {
            abort(404);
        }

        if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') && (isset($commission->invoice_data['product_tax_code']) || isset($commission->parentInvoiceData['product_tax_code']))) {
            // Retrieve information for the current tax code, for convenience
            $taxCode = (new StripeClient([
                'api_key'        => config('aldebaran.commissions.payment_processors.stripe.integration.secret_key'),
                'stripe_version' => '2022-11-15',
            ]))->taxCodes->retrieve(
                $commission->invoice_data['product_tax_code'] ?? $commission->parentInvoiceData['product_tax_code']
            );
        }

        return view('admin.queues.commission', [
            'commission' => $commission,
        ] + ($commission->status == 'Pending' || $commission->status == 'Accepted' ? [
            'pieces'  => Piece::sort()->pluck('name', 'id')->toArray(),
            'taxCode' => $taxCode ?? null,
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
    public function postCommission($id, $action, Request $request, CommissionManager $service) {
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
                return $this->postBanCommissioner($id, $request->only(['comments']) + ['context' => 'commission'], $request, $service);
                break;
        }

        return redirect()->back();
    }

    /**
     * Gets the send invoice modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSendInvoice($id) {
        $payment = CommissionPayment::find($id);

        return view('admin.queues._send_invoice', [
            'payment' => $payment,
        ]);
    }

    /**
     * Sends an invoice.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSendInvoice(Request $request, CommissionManager $service, $id) {
        if ($id && $service->sendInvoice(CommissionPayment::where('id', $id)->first(), $request->user())) {
            flash('Invoice sent successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Handles incoming events from a Stripe webhook.
     * Largely echoes https://stripe.com/docs/webhooks/quickstart.
     */
    public function postStripeWebhook(CommissionManager $service) {
        Stripe::setApiKey(config('aldebaran.commissions.payment_processors.stripe.integration.secret_key'));
        Stripe::setApiVersion('2022-11-15');

        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = Event::constructFrom(json_decode($payload, true));
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe webhook error while parsing basic request.');
            http_response_code(400);
            exit();
        }

        if (config('aldebaran.commissions.payment_processors.stripe.integration.webhook_secret')) {
            // Only verify the event if there is an endpoint secret defined
            // Otherwise use the basic decoded event
            $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            try {
                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    config('aldebaran.commissions.payment_processors.stripe.integration.webhook_secret')
                );
            } catch (SignatureVerificationException $e) {
                // Invalid signature
                Log::error('Stripe webhook error while validating signature.');
                http_response_code(400);
                exit();
            }
        }

        // Handle the event
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
            default:
                // Unexpected event type
                Log::notice('Stripe webhook received unknown event type.');
        }

        // Return a successful response to Stripe
        http_response_code(200);

        // Update the relevant payment
        if (isset($invoice) && $invoice) {
            $service->processPaidInvoice($invoice);
        }
    }

    /**
     * Handles incoming events from a PayPal webhook.
     */
    public function postPaypalWebhook(CommissionManager $service) {
        $payload = @file_get_contents('php://input');
        $event = json_decode($payload, true);
        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_UPPER);

        // Check that the certificate comes from PayPal
        if (preg_match('/https:\/\/api.sandbox.paypal.com\//', (string) $headers['PAYPAL-CERT-URL']) || preg_match('/https:\/\/api.paypal.com\//', (string) $headers['PAYPAL-CERT-URL']) || preg_match('/https:\/\/paypal.com\//', (string) $headers['PAYPAL-CERT-URL'])) {
            $verifyUrl = true;
        } else {
            $verifyUrl = false;
            Log::error('Certificate URL ('.(string) $headers['PAYPAL-CERT-URL'].') does not appear to originate from PayPal.');
        }

        // Verify the webhook signature
        if ($verifyUrl && openssl_verify(
            data: implode(separator: '|', array: [
                $headers['PAYPAL-TRANSMISSION-ID'],
                $headers['PAYPAL-TRANSMISSION-TIME'],
                config('aldebaran.commissions.payment_processors.paypal.integration.webhook_id'),
                crc32(string: $payload),
            ]),
            signature: base64_decode(string: $headers['PAYPAL-TRANSMISSION-SIG']),
            public_key: openssl_pkey_get_public(public_key: file_get_contents(filename: $headers['PAYPAL-CERT-URL'])),
            algorithm: 'sha256WithRSAEncryption'
        ) === 1) {
            Log::info('PayPal webhook signature verified successfully.');
        } else {
            Log::error('Error verifying PayPal webhook event signature.');
            exit();
        }

        // Handle the event
        switch ($event['event_type']) {
            case 'INVOICING.INVOICE.PAID':
                $invoice = $event['resource']['invoice'];

                // Attempt to verify payment completion, and if not, defer processing
                // Pending payments do not have final payment data (minus fees, etc.)
                // This depends on PayPal resending the event if not received successfully
                if (isset($invoice['payments']['transactions'][0]['payment_id'])) {
                    // Initialize PayPal client
                    $paypal = new PayPalClient;
                    $paypal->getAccessToken();

                    $capturedPayment = $paypal->showCapturedPaymentDetails($invoice['payments']['transactions'][0]['payment_id']);
                    if (isset($capturedPayment['debug_id'])) {
                        Log::error('Failed to locate payment information.');
                        exit();
                    } elseif ($capturedPayment['status'] == 'PENDING') {
                        Log::notice('Payment pending. Deferring processing.');
                        exit();
                    }
                }
            default:
                // Unexpected event type
                Log::notice('PayPal webhook received unknown event type.');
        }

        // Return a successful response to PayPal
        http_response_code(200);

        // Update the relevant payment
        if (isset($invoice) && $invoice) {
            $service->processPaidInvoice($invoice);
        }
    }

    /**
     * Shows the quote index page.
     *
     * @param string $status
     * @param mixed  $class
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQuoteIndex(Request $request, $class, $status = null) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $class = CommissionClass::where('slug', $class)->first();
        if (!$class) {
            abort(404);
        }

        $quotes = CommissionQuote::with('commissioner')->class($class->id)->where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['commission_type', 'sort']);
        if (isset($data['commission_type']) && $data['commission_type'] != 'none') {
            $quotes->where('commission_type_id', $data['commission_type']);
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $quotes->orderBy('created_at', 'DESC');
                    break;
                case 'oldest':
                    $quotes->orderBy('created_at', 'ASC');
                    break;
            }
        } else {
            $quotes->orderBy('created_at');
        }

        return view('admin.queues.quote_index', [
            'quotes'      => $quotes->paginate(30)->appends($request->query()),
            'types'       => ['none' => 'Any Type'] + CommissionType::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'class'       => $class,
            'count'       => new CommissionType,
        ]);
    }

    /**
     * Show the new quote request form.
     *
     * @param string $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewQuote($slug) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $class = CommissionClass::where('slug', $slug)->first();
        if (!$class) {
            abort(404);
        }

        $commissioners = Commissioner::valid()->get()->pluck('fullName', 'id')->sort()->toArray();

        return view('admin.queues.new_quote', [
            'class'         => $class,
            'commissioners' => $commissioners,
            'types'         => CommissionType::class($class->id)->get()->pluck('fullName', 'id')->toArray(),
            'quote'         => new CommissionQuote,
        ]);
    }

    /**
     * Submits a new quote request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewQuote(Request $request, CommissionManager $service) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $type = CommissionType::where('id', $request->get('commission_type_id'))->first();
        if (!$type) {
            abort(404);
        }

        $request->validate(CommissionQuote::$manualCreateRules);

        $data = $request->only([
            'commissioner_id', 'name', 'email', 'contact',
            'commission_type_id', 'subject', 'description', 'amount',
        ]);
        if ($quote = $service->createQuote($data, true)) {
            flash('Quote submitted successfully.')->success();

            return redirect()->to('admin/commissions/quotes/edit/'.$quote->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the quote detail page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQuote($id) {
        $quote = CommissionQuote::findOrFail($id);
        if (!$quote) {
            abort(404);
        }

        return view('admin.queues.quote', [
            'quote' => $quote,
        ]);
    }

    /**
     * Acts on a quote.
     *
     * @param int    $id
     * @param string $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postQuote($id, $action, Request $request, CommissionManager $service) {
        switch ($action) {
            default:
                flash('Invalid action selected.')->error();
                break;
            case 'accept':
                return $this->postAcceptQuote($id, $request, $service);
                break;
            case 'update':
                return $this->postUpdateQuote($id, $request, $service);
                break;
            case 'complete':
                return $this->postCompleteQuote($id, $request, $service);
                break;
            case 'decline':
                return $this->postDeclineQuote($id, $request, $service);
                break;
            case 'ban':
                return $this->postBanCommissioner($id, $request->only(['comments']) + ['context' => 'quote'], $request, $service);
                break;
        }

        return redirect()->back();
    }

    /**
     * Show the ledger.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLedger(Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        $yearCommissions = Commission::whereIn('status', ['Accepted', 'Complete'])->orderBy('created_at', 'DESC')->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y');
        });

        $yearPayments = CommissionPayment::orderBy('paid_at', 'DESC')->orderBy('created_at', 'DESC')->with('commission.commissioner')->get()->filter(function ($payment) {
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

                return Carbon::now()->format('F Y');
            })->sort(function ($paymentsA, $paymentsB) {
                // Sort by month, numerically
                // As the payments have already been grouped, it's safe to just take the value from the first
                $monthA = $paymentsA->first()->paid_at ? $paymentsA->first()->paid_at->month : Carbon::now()->month;
                $monthB = $paymentsB->first()->paid_at ? $paymentsB->first()->paid_at->month : Carbon::now()->month;

                if ($monthB > $monthA) {
                    return 1;
                }

                return 0;
            });
        });

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
    private function postAcceptCommission($id, Request $request, CommissionManager $service) {
        if ($service->acceptCommission($id, $request->only(['comments']), $request->user())) {
            flash('Commission accepted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
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
    private function postUpdateCommission($id, Request $request, CommissionManager $service) {
        $request->validate(Commission::$updateRules);
        $data = $request->only([
            'pieces', 'paid_status', 'progress', 'comments', 'cost', 'tip', 'is_paid', 'is_intl', 'paid_at', 'total_with_fees', 'invoice_id',
            'product_name', 'product_description', 'product_tax_code', 'product_category', 'unset_product_info', 'send_notification',
        ]);
        if ($service->updateCommission($id, $data, $request->user())) {
            flash('Commission updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
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
    private function postCompleteCommission($id, Request $request, CommissionManager $service) {
        if ($service->completeCommission($id, $request->only(['comments']), $request->user())) {
            flash('Commission marked complete successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
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
    private function postDeclineCommission($id, Request $request, CommissionManager $service) {
        if ($service->declineCommission($id, $request->only(['comments']), $request->user())) {
            flash('Commission declined successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Accepts a quote.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAcceptQuote($id, Request $request, CommissionManager $service) {
        if ($service->acceptQuote($id, $request->only(['comments']), $request->user())) {
            flash('Quote accepted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Updates a quote.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postUpdateQuote($id, Request $request, CommissionManager $service) {
        $request->validate(Commission::$updateRules);
        $data = $request->only([
            'amount', 'comments', 'send_notification',
        ]);
        if ($service->updateQuote($id, $data, $request->user())) {
            flash('Quote updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Marks a quote complete.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postCompleteQuote($id, Request $request, CommissionManager $service) {
        if ($service->completeQuote($id, $request->only(['comments']), $request->user())) {
            flash('Quote marked complete successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Declines a quote.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postDeclineQuote($id, Request $request, CommissionManager $service) {
        if ($service->declineQuote($id, $request->only(['comments']), $request->user())) {
            flash('Quote declined successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Accepts a commission.
     *
     * @param int   $id
     * @param array $data
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postBanCommissioner($id, $data, Request $request, CommissionManager $service) {
        if ($service->banCommissioner($id, $data, $request->user())) {
            flash('Commissioner banned successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }
}
