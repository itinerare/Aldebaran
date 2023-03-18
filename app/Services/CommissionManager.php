<?php

namespace App\Services;

use App\Facades\Settings;
use App\Mail\CommissionRequested;
use App\Models\Commission\Commission;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionerIp;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\MailingList\MailingListSubscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class CommissionManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Commission Manager
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of commissions and commission data.
    |
    */

    /**
     * Creates a new commission request.
     *
     * @param array $data
     * @param bool  $manual
     *
     * @return \App\Models\Commission\Commission|bool
     */
    public function createCommission($data, $manual = false) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.commissions.enabled')) {
                throw new \Exception('Commissions are not enabled for this site.');
            }

            // Verify the type and, if necessary, key
            $type = CommissionType::where('id', $data['type'])->first();
            if (!$type) {
                throw new \Exception('The selected commission type is invalid.');
            }
            if (!$manual) {
                if (!$type->category->class->is_active) {
                    throw new \Exception('This class is inactive.');
                }
                // Check that commissions are open for this type and for the class
                if (!Settings::get($type->category->class->slug.'_comms_open')) {
                    throw new \Exception('Commissions are not open.');
                }
                if (!$type->category->is_active) {
                    throw new \Exception('Commissions are not open for this category.');
                }
                if (!$type->is_active) {
                    throw new \Exception('Commissions are not open for this type.');
                }
                // If the commission type is currently hidden, check for the presence of
                // the key, and if so, check it
                if (!$type->is_visible && (!isset($data['key']) || $type->key != $data['key'])) {
                    throw new \Exception('Commissions are not open for this type.');
                }
                if ($type->availability > 0 && $type->currentSlots == 0) {
                    throw new \Exception('Commission slots for this type are full.');
                }
                // Check that there is a free slot for the type and/or class
                if (is_int($type->getSlots($type->category->class)) && $type->getSlots($type->category->class) == 0) {
                    throw new \Exception('Overall commission slots are full.');
                }
                // Check that the selected payment processor is enabled
                if (isset($data['payment_processor']) && !config('aldebaran.commissions.payment_processors.'.$data['payment_processor'].'.enabled')) {
                    throw new \Exception('This payment processor is not currently accepted.');
                }
            }

            if (isset($data['commissioner_id'])) {
                $commissioner = Commissioner::where('id', $data['commissioner_id'])->first();
                if (!$commissioner) {
                    throw new \Exception('Invalid commissioner selected.');
                }
            } else {
                $commissioner = $this->processCommissioner($data, $manual ? false : true);
            }

            // Collect and form responses related to the commission itself
            foreach ($type->formFields as $key=> $field) {
                if (isset($data[$key])) {
                    if ($field['type'] != 'multiple') {
                        $data['data'][$key] = strip_tags($data[$key]);
                    } elseif ($field['type'] == 'multiple') {
                        $data['data'][$key] = $data[$key];
                    }
                }
            }

            if (isset($data['additional_information'])) {
                $data['data']['additional_information'] = $data['additional_information'];
            }

            if (!isset($data['data'])) {
                $data['data'] = null;
            }

            $commission = Commission::create([
                'commissioner_id'   => $commissioner->id,
                'commission_type'   => $type->id,
                'status'            => 'Pending',
                'data'              => $data['data'],
                'payment_processor' => $data['payment_processor'],
            ]);

            // Now that the commission has an ID, assign it a key incorporating it
            // This ensures that even in the very odd case of a duplicate key,
            // conflicts should not arise
            $commission->update(['commission_key' => $commission->id.'_'.randomString(15)]);

            // If desired, send an email notification to the admin account
            // that a commission request was submitted
            if (config('aldebaran.settings.email_features') && Settings::get('notif_emails') && !$manual && (config('aldebaran.settings.admin_email.address') && config('aldebaran.settings.admin_email.password'))) {
                Mail::to(User::find(1))->send(new CommissionRequested($commission));
            }

            return $this->commitReturn($commission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Accepts a commission.
     *
     * @param int                   $id
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function acceptCommission($id, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if (!$user) {
                throw new \Exception('Invalid user.');
            }
            // Check that the commission exists and is pending
            $commission = Commission::where('id', $id)->where('status', 'Pending')->first();
            if (!$commission) {
                throw new \Exception('Invalid commission selected.');
            }
            // Check that this commission will not be in excess of any slot limitations
            if (Settings::get($commission->type->category->class->slug.'_overall_slots') > 0 || $commission->type->slots != null) {
                if (is_int($commission->type->getSlots($commission->type->category->class)) && $commission->type->getSlots($commission->type->category->class) == 0) {
                    throw new \Exception('There are no overall slots of this commission type remaining.');
                }
                if ($commission->type->availability > 0 && $commission->type->currentSlots == 0) {
                    throw new \Exception('This commission type\'s slots are full.');
                }
            }

            // Update the commission status and comments
            $commission->update([
                'status'   => 'Accepted',
                'comments' => $data['comments'] ?? null,
            ]);

            // If this is the last available commission slot overall or for this type,
            // automatically decline any remaining pending requests
            if (Settings::get($commission->type->category->class->slug.'_overall_slots') > 0 || $commission->type->slots != null) {
                // Overall slots filled
                if (is_int($commission->type->getSlots($commission->type->category->class)) && $commission->type->getSlots($commission->type->category->class) == 0) {
                    Commission::class($commission->type->category->class->id)->where('status', 'Pending')->update(['status' => 'Declined', 'comments' => '<p>Sorry, all slots have been filled! '.Settings::get($commission->type->category->class->slug.'_full').'</p>']);
                }
                // Type slots filled
                elseif ($commission->type->availability > 0 && ($commission->type->currentSlots - 1) <= 0) {
                    Commission::where('commission_type', $commission->type->id)->where('status', 'Pending')->update(['status' => 'Declined', 'comments' => '<p>Sorry, all slots for this commission type have been filled! '.Settings::get($commission->type->category->class->slug.'_full').'</p>']);
                }
            }

            return $this->commitReturn($commission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a commission.
     *
     * @param int                   $id
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function updateCommission($id, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if (!$user) {
                throw new \Exception('Invalid user.');
            }
            // Check that the commission exists and is accepted
            $commission = Commission::where('id', $id)->where('status', 'Accepted')->first();
            if (!$commission) {
                throw new \Exception('Invalid commission selected.');
            }

            // Process data as necessary
            if (isset($data['pieces'])) {
                // Check that all selected pieces exist
                $pieces = Piece::whereIn('id', $data['pieces'])->get();
                if (count($data['pieces']) != $pieces->count()) {
                    throw new \Exception('One or more of the selected pieces is invalid.');
                }

                // Clear old pieces
                $commission->pieces()->delete();

                // Create commission piece record for each piece
                foreach ($pieces as $piece) {
                    $commission->pieces()->create([
                        'piece_id' => $piece->id,
                    ]);
                }
            } elseif ($commission->pieces->count()) {
                // Clear old pieces
                $commission->pieces()->delete();
            }

            // Process payment data
            if (isset($data['cost'])) {
                // Clear old payments
                $commission->payments()->delete();

                // Create payment record for each
                foreach ($data['cost'] as $key => $cost) {
                    $payment = $commission->payments()->create([
                        'cost'            => $cost,
                        'tip'             => $data['tip'][$key] ?? null,
                        'is_paid'         => $data['is_paid'][$key] ?? 0,
                        'is_intl'         => $data['is_intl'][$key] ?? 0,
                        'paid_at'         => isset($data['is_paid'][$key]) && $data['is_paid'][$key] ? ($data['paid_at'][$key] ?? Carbon::now()) : null,
                        'total_with_fees' => isset($data['is_paid'][$key]) && $data['is_paid'][$key] ? ($data['total_with_fees'][$key] ?? CommissionPayment::calculateAdjustedTotal($cost, $data['tip'][$key], $data['is_intl'][$key] ?? 0, $commission->payment_processor)) : null,
                        'invoice_id'      => $data['invoice_id'][$key] ?? null,
                    ]);
                }
            } elseif ($commission->payments->count()) {
                // Clear old payment records
                $commission->payments()->delete();
            }

            if (isset($data['product_name']) && !isset($data['product_tax_code'])) {
                // The tax category code is not automatically inherited,
                // which could be counter-intuitive if creating products different
                // from the Stripe account's default settings
                $data['product_tax_code'] = $commission->parentInvoiceData['product_tax_code'] ?? null;
            }
            $data = (new CommissionService)->processInvoiceData($data);

            // Update the commission
            $commission->update(Arr::only($data, [
                'progress', 'status', 'description', 'data', 'comments', 'invoice_data',
            ]));

            return $this->commitReturn($commission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sends an invoice for a payment.
     *
     * @param \App\Models\Commission\CommissionPayment $payment
     * @param \App\Models\User\User                    $user
     *
     * @return mixed
     */
    public function sendInvoice($payment, $user) {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if (!$user) {
                throw new \Exception('Invalid user.');
            }

            // Check that the payment exists and is valid
            if (!$payment) {
                throw new \Exception('Invalid payment selected.');
            }
            if ($payment->is_paid) {
                throw new \Exception('This payment has already been paid.');
            }
            if (isset($payment->invoice_id)) {
                throw new \Exception('An invoice has already been sent for this payment.');
            }

            // Determine the relevant product information
            $product = [];
            $product['name'] = $payment->commission->invoice_data['product_name'] ?? ($payment->commission->parentInvoiceData['product_name'] ?? null);
            $product['description'] = $payment->commission->invoice_data['description'] ?? ($payment->commission->parentInvoiceData['description'] ?? null);
            $product['tax_code'] = $payment->commission->invoice_data['product_tax_code'] ?? ($payment->commission->parentInvoiceData['product_tax_code'] ?? null);

            // Check that the product name is retrieved,
            // as this is the only part which the site requires
            if (!isset($product['name'])) {
                throw new \Exception('Failed to locate product information.');
            }

            // Depending on payment processor, perform further checks
            // and if possible, send an invoice and update the payment appropriately
            switch ($payment->commission->payment_processor) {
                case 'stripe':
                    if (!config('aldebaran.commissions.payment_processors.stripe.integration.enabled')) {
                        throw new \Exception('Stripe integration features are not enabled for this site.');
                    }

                    // Initialize a connection to the Stripe API
                    $stripe = new StripeClient([
                        'api_key'        => config('aldebaran.commissions.payment_processors.stripe.integration.secret_key'),
                        'stripe_version' => '2022-11-15',
                    ]);

                    // Locate or create and store a new customer
                    if (isset($payment->commission->commissioner->customer_id)) {
                        $customer = $stripe->customers->retrieve($payment->commission->commissioner->customer_id);
                    } else {
                        $customer = $stripe->customers->create([
                            'email' => $payment->commission->commissioner->payment_email,
                        ]);

                        $payment->commission->commissioner->update([
                            'customer_id' => $customer['id'],
                        ]);
                    }

                    if (!isset($customer) || !$customer) {
                        throw new \Exception('Failed to create or retrieve customer information');
                    }

                    // And an invoice
                    $invoice = $stripe->invoices->create([
                        'customer'          => $customer['id'],
                        'collection_method' => 'send_invoice',
                        'days_until_due'    => config('aldebaran.commissions.payment_processors.stripe.integration.invoices_due'),
                        'auto_advance'      => false,
                        'currency'          => strtolower(config('aldebaran.commissions.currency')),
                    ]);

                    // Create an invoice item
                    $invoiceItem = $stripe->invoiceItems->create([
                        'invoice'      => $invoice['id'],
                        'customer'     => $customer['id'],
                        'description'  => $product['name'],
                        'quantity'     => 1,
                        'unit_amount'  => (int) ($payment->cost * 100),
                        // Amount must be an int expressed in cents
                    ] + (isset($product['tax_code']) ? [
                        'tax_code' => $product['tax_code'],
                    ] : []));

                    // Send the invoice
                    $stripe->invoices->sendInvoice($invoice['id']);

                    // Update the payment with the invoice ID
                    $payment->update([
                        'invoice_id' => $invoice['id'],
                    ]);
                    break;
                case 'paypal':
                    if (!config('aldebaran.commissions.payment_processors.paypal.integration.enabled')) {
                        throw new \Exception('PayPal integration features are not enabled for this site.');
                    }

                    // Initialize a connection to the PayPal API and set some values
                    $paypal = new PayPalClient;
                    $paypal->setCurrency(config('aldebaran.commissions.currency'));

                    // This requests PayPal return the full contents of e.g. the created invoice
                    $paypal->setRequestHeader('Prefer', 'return=representation');

                    // Get an access token; this is required to interact with the API
                    $paypal->getAccessToken();

                    // If the logo image is stored by default as a WebP,
                    // and there is not already a PNG copy stored, create one
                    // as PayPal will not accept WebP images
                    if (config('aldebaran.settings.image_formats.site_images') == 'webp' && !file_exists(public_path().'/images/assets/logo.png')) {
                        Image::make(public_path().'/images/assets/logo.webp')->save(public_path().'/images/assets/logo.png', null, 'png');
                    }

                    // Set up invoice data
                    $invoiceData = [
                        'detail' => [
                            'currency_code'        => config('aldebaran.commissions.currency'),
                            'terms_and_conditions' => url('commissions/'.$payment->commission->type->category->class->slug.'/tos'),
                        ],
                        'invoicer' => [
                            'website'       => config('app.url'),
                            'logo_url'      => config('app.env') == 'production' ? url('images/assets/logo.'.(config('aldebaran.settings.image_formats.site_images') == 'webp' ? 'png' : config('aldebaran.settings.image_formats.site_images'))) : null,
                        ],
                        'primary_recipients' => [
                            [
                                'billing_info' => [
                                    'email_address' => $payment->commission->commissioner->payment_email,
                                ],
                            ],
                        ],
                        'items' => [
                            [
                                'name'        => $product['name'],
                                'quantity'    => 1,
                                'unit_amount' => [
                                    'currency_code' => config('aldebaran.commissions.currency'),
                                    'value'         => $payment->cost,
                                ],
                                'description' => $product['description'] ?? null,
                            ],
                        ],
                        'configuration' => [
                            'allow_tip' => true,
                        ],
                        'status' => 'DRAFT',
                    ];

                    // Create the draft invoice
                    $invoice = $paypal->createInvoice($invoiceData);

                    // Attempt to send the invoice
                    $status = json_decode($paypal->sendInvoice($invoice['id']), true);
                    if (isset($status['debug_id'])) {
                        throw new \Exception('An error occurred sending invoice.');
                    }

                    // Update the payment with the invoice ID
                    $payment->update([
                        'invoice_id' => $invoice['id'],
                    ]);
                    break;
            }

            return $this->commitReturn($payment);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes notification of a paid invoice from a payment processor.
     *
     * @param mixed $invoice
     *
     * @return bool
     */
    public function processPaidInvoice($invoice) {
        DB::beginTransaction();

        try {
            // Identify the relevant payment
            $payment = CommissionPayment::where('invoice_id', $invoice['id'])->first();

            if (!$payment) {
                Log::notice('No payment found for webhook invoice.', [
                    'invoice' => $invoice['id'],
                ]);
            }

            // It's possible that this catches irrelevant events,
            // so rather than throwing an error on failing to identify a payment,
            // only proceed if a payment is found
            if ($payment) {
                // Otherwise, check that the payment is unpaid
                if ($payment->is_paid) {
                    Log::error('Payment matches incoming invoice, but is already marked paid.', [
                        'payment' => $payment->id,
                        'invoice' => $invoice['id'],
                    ]);

                    return false;
                }

                switch ($payment->commission->payment_processor) {
                    case 'stripe':
                        Stripe::setApiKey(config('aldebaran.commissions.payment_processors.stripe.integration.secret_key'));
                        Stripe::setApiVersion('2022-11-15');

                        // Retrieve the processing fee via payment intent
                        $fee = PaymentIntent::retrieve([
                            'id'     => $invoice['payment_intent'],
                            'expand' => ['latest_charge.balance_transaction'],
                        ])->latest_charge->balance_transaction->fee_details[0]->amount;

                        $payment->update([
                            'is_paid'         => 1,
                            'paid_at'         => Carbon::now(),
                            'total_with_fees' => ($invoice['total'] - $fee) / 100,
                        ]);
                        break;
                    case 'paypal':
                        // Retrieve the processing fee via transaction
                        $transactionId = $invoice['payments']['transactions'][0]['payment_id'] ?? null;

                        // Initialize PayPal client
                        $paypal = new PayPalClient;
                        $paypal->getAccessToken();

                        // Attempt to locate payment info
                        $capturedPayment = $paypal->showCapturedPaymentDetails($transactionId);
                        $authorizedPayment = $paypal->showAuthorizedPaymentDetails($transactionId);
                        if (isset($capturedPayment['debug_id']) && isset($authorizedPayment['debug_id'])) {
                            Log::error('Failed to locate payment information.');

                            return false;
                        }

                        // Retrieve total after fees for payment
                        $net = $capturedPayment['seller_receivable_breakdown']['net_amount']['value'] ?? ($authorizedPayment['seller_receivable_breakdown']['net_amount']['value'] ?? null);

                        if (!$net) {
                            Log::error('Failed to locate net total.');

                            return false;
                        }

                        $payment->update([
                            'is_paid'         => 1,
                            'paid_at'         => Carbon::now(),
                            'tip'             => $invoice['gratuity']['value'] ?? 0.00,
                            'total_with_fees' => $net,
                        ]);
                        break;
                    default:
                        Log::error('Attempted to process a paid invoice for a commission using a non-supported payment processor.', [
                            'payment' => $payment->id,
                            'invoice' => $invoice['id'],
                        ]);
                }

                return $this->commitReturn($payment);
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Marks a commission complete.
     *
     * @param int                   $id
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function completeCommission($id, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if (!$user) {
                throw new \Exception('Invalid user.');
            }
            // Check that the commission exists and is accepted
            $commission = Commission::where('id', $id)->where('status', 'Accepted')->first();
            if (!$commission) {
                throw new \Exception('Invalid commission selected.');
            }

            // Update the commission status and comments
            $commission->update([
                'status'   => 'Complete',
                'progress' => 'Finished',
                'comments' => $data['comments'] ?? null,
            ]);

            return $this->commitReturn($commission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Declines a commission.
     *
     * @param int                   $id
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return mixed
     */
    public function declineCommission($id, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if (!$user) {
                throw new \Exception('Invalid user.');
            }
            // Check that the commission exists and is pending
            $commission = Commission::where('id', $id)->whereIn('status', ['Pending', 'Accepted'])->first();
            if (!$commission) {
                throw new \Exception('Invalid commission selected.');
            }

            // Update the commission status and comments
            $commission->update([
                'status'   => 'Declined',
                'comments' => $data['comments'] ?? null,
            ]);

            return $this->commitReturn($commission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles bans from the commission or mailing list systems.
     *
     * @param int|string            $subject
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Commission\Commission|bool
     */
    public function banCommissioner($subject, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if (!$user) {
                throw new \Exception('Invalid user.');
            }
            if (is_numeric($subject)) {
                // Fetch commission so as to fetch commissioner
                $commission = Commission::where('id', $subject)->whereIn('status', ['Pending', 'Accepted'])->first();
                if (!$commission) {
                    throw new \Exception('Invalid commission selected.');
                }
                $commissioner = Commissioner::where('id', $commission->commissioner_id)->first();
                if (!$commissioner) {
                    throw new \Exception('Invalid commissioner selected.');
                }
            } elseif (is_string($subject)) {
                // Locate existing commissioner if extant
                if (Commissioner::where('email', $subject)->exists()) {
                    $commissioner = Commissioner::where('email', $subject);
                } else {
                    // Otherwise create a new commissioner to hold the ban
                    $commissioner = Commissioner::create([
                        'email'         => $subject,
                        'payment_email' => $subject,
                        'name'          => 'Banned Subscriber '.$subject,
                        'is_banned'     => 1,
                    ]);
                }
            }

            // Mark the commissioner as banned,
            $commissioner->update(['is_banned' => 1]);
            // and decline all current commission requests from them
            Commission::where('commissioner_id', $commissioner->id)->whereIn('status', ['Pending', 'Accepted'])->update(['status' => 'Declined', 'comments' => $data['comments'] ?? '<p>Automatically declined due to ban.</p>']);

            // Also delete any present mailing list subscriptions, if relevant
            MailingListSubscriber::where('email', $commissioner->email)->delete();

            return $this->commitReturn($commission ?? true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating commissioner data.
     *
     * @param array $data
     * @param bool  $processIp
     *
     * @return array
     */
    private function processCommissioner($data, $processIp = true) {
        // Attempt to fetch commissioner, first by email, then by IP as a fallback
        // Failing these, create a new commissioner
        $commissioner = Commissioner::where('email', $data['email'])->first();
        if (!$commissioner && $processIp) {
            // Fetch by IP to check for ban
            $ipCommissioner = CommissionerIp::where('ip', $data['ip'])->first() ? CommissionerIp::where('ip', $data['ip'])->first()->commissioner : null;
            if ($ipCommissioner && $ipCommissioner->id) {
                if ($ipCommissioner->is_banned) {
                    throw new \Exception('Unable to submit commission request. You are banned.');
                }
            }
        }

        // Update existing commissioner information
        if ($commissioner && $commissioner->id) {
            // Check for ban
            if ($commissioner->is_banned) {
                throw new \Exception('Unable to submit commission request. You are banned.');
            }

            $commissioner->update([
                'email'         => (isset($data['email']) && $data['email'] != $commissioner->email ? $data['email'] : $commissioner->email),
                'name'          => (isset($data['name']) && $data['name'] != $commissioner->getRawOriginal('name') ? $data['name'] : $commissioner->name),
                'contact'       => (isset($data['contact']) && $data['contact'] != $commissioner->contact ? strip_tags($data['contact']) : $commissioner->contact),
                'payment_email' => (isset($data['payment_email']) && $data['payment_email'] != $commissioner->payment_email ? $data['payment_email'] : $commissioner->payment_email),
            ]);
        }
        // Create commissioner information
        else {
            $commissioner = Commissioner::create([
                'name'          => $data['name'] ?? null,
                'email'         => $data['email'],
                'contact'       => strip_tags($data['contact']),
                'payment_email' => $data['payment_email'] ?? $data['email'],
            ]);
        }

        // If commissioner and/or IP are new, process IP data
        if ($processIp && !$commissioner->ips->where('ip', $data['ip'])->first()) {
            CommissionerIp::create([
                'commissioner_id' => $commissioner->id,
                'ip'              => $data['ip'],
            ]);
        }

        return $commissioner;
    }
}
