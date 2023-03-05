<?php

namespace App\Services;

use App\Mail\VerifyMailingListSubscription;
use App\Models\Commission\Commissioner;
use App\Models\MailingList\MailingList;
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
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled for this site.');
            }

            $mailingList = MailingList::open()->where('id', $data['mailing_list_id'])->first();
            if (!$mailingList) {
                throw new \Exception('Invalid mailing list selected.');
            }

            // Only create a subscriber/send a verification email if one does not already exist
            // However, do not advertise this fact so as not to advertise who is/is not subscribed inadvertently
            if (!MailingListSubscriber::where('email', $data['email'])->exists()) {
                if (Commissioner::where('email', $data['email'])->valid(1)->exists()) {
                    throw new \Exception('Unable to subscribe. This address has been banned.');
                }

                $data['token'] = randomString(15);
                $subscriber = MailingListSubscriber::create($data);

                // Send the subscription verification email
                Mail::to($subscriber->email)
                    ->send(new VerifyMailingListSubscription($subscriber));

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
            // Check that the subscriber is not already verified
            if ($subscriber->is_verified) {
                throw new \Exception('Your subscription is already verified!');
            }

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
     * Unsubscribes.
     *
     * @param \App\Models\MailingList\MailingListSubscriber $subscriber
     * @param string                                        $token
     *
     * @return \App\Models\MailingList\MailingListSubscriber|bool
     */
    public function removeSubscriber($subscriber, $token) {
        DB::beginTransaction();

        try {
            // Perform a secondary check of the token
            if ($subscriber->token != $token) {
                throw new \Exception('Invalid token.');
            }

            $subscriber->delete();

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
