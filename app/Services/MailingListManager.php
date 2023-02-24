<?php

namespace App\Services;

use App\Mail\VerifyMailingListSubscription;
use App\Models\Commission\Commissioner;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailingListManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Mailing List Manager
    |--------------------------------------------------------------------------
    |
    | Handles subscription and unsubscription from mailing lists.
    |
    */

    /**
     * Creates a subscriber.
     *
     * @param array $data
     *
     * @return \App\Models\MailingList\MailingListSubscriber|bool
     */
    public function createSubscriber($data) {
        DB::beginTransaction();

        try {
            // Only create a subscriber/send a verification email if one does not already exist
            // However, do not advertise this fact so as not to advertise who is/is not subscribed inadvertently
            if (!MailingListSubscriber::where('email', $data['email'])->exists()) {
                if (Commissioner::where('email', $data['email'])->valid(1)->exists()) {
                    throw new \Exception('Unable to subscribe. This address has been banned.');
                }

                $data['token'] = randomString(15);
                $subscriber = MailingListSubscriber::create($data);

                // Send the subscription verification email
                if (!Mail::to($subscriber->email)->send(new VerifyMailingListSubscription($subscriber))) {
                    throw new \Exception('Failed to send verification email.');
                }

                return $this->commitReturn($subscriber);
            } else {
                return $this->commitReturn(true);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Verifies a subscription.
     *
     * @param \App\Models\MailingList\MailingListSubscriber $subscriber
     * @param string                                        $token
     *
     * @return \App\Models\MailingList\MailingListSubscriber|bool
     */
    public function verifySubscriber($subscriber, $token) {
        DB::beginTransaction();

        try {
            // Perform a secondary check of the token
            if ($subscriber->token != $token) {
                throw new \Exception('Invalid token.');
            }

            $subscriber->update([
                'is_verified' => 1,
                'token'       => randomString(15),
            ]);

            return $this->commitReturn($subscriber);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Kicks a subscriber.
     *
     * @param \App\Models\MailingList\MailingListSubscriber $subscriber
     *
     * @return bool
     */
    public function kickSubscriber($subscriber) {
        DB::beginTransaction();

        try {
            $subscriber->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
