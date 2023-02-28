<?php

namespace App\Http\Controllers;

use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListSubscriber;
use App\Services\MailingListManager;
use Illuminate\Http\Request;

class MailingListController extends Controller {
    /**
     * Show a mailing list's page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMailingList($id) {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        $mailingList = MailingList::open()->where('id', $id)->first();
        if (!$mailingList) {
            abort(404);
        }

        return view('mailing_lists.subscribe', [
            'mailingList' => $mailingList,
        ]);
    }

    /**
     * Processes an initial mailing list subscription.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSubscribe(Request $request, MailingListManager $service, $id) {
        $validationRules = MailingListSubscriber::$createRules;
        // If the app is running in a prod environment,
        // validate recaptcha response as well
        if (config('app.env') == 'production' && config('aldebaran.settings.captcha')) {
            $validationRules['g-recaptcha-response'] = 'required|recaptchav3:submit,0.5';
        }
        $request->validate($validationRules);

        $data = $request->only(['email']) + ['mailing_list_id' => $id];

        if ($service->createSubscriber($data)) {
            flash('Success! A verification email has been sent to the provided address.')->success();

            return redirect()->to('mailing-lists/'.$id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Process subscription verification.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getVerify(Request $request, MailingListManager $service, $id) {
        $subscriber = MailingListSubscriber::where('id', $id)->where('token', $request->get('token'))->first();
        if (!$subscriber) {
            abort(404);
        }

        if ($service->verifySubscriber($subscriber, $request->get('token'))) {
            flash('Success! Your subscription is now verified.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        // Verification should be possible regardless of whether email features
        // are presently enabled, the mailing list is open, etc
        if (config('aldebaran.settings.email_features') && $subscriber->mailingList->is_open) {
            return redirect()->to('mailing-lists/'.$subscriber->mailing_list_id);
        } else {
            return redirect()->to('/');
        }
    }

    /**
     * Process unsubscription.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUnsubscribe(Request $request, MailingListManager $service, $id) {
        $subscriber = MailingListSubscriber::where('id', $id)->where('token', $request->get('token'))->first();
        if (!$subscriber) {
            abort(404);
        }

        if ($service->removeSubscriber($subscriber, $request->get('token'))) {
            flash('You have successfully unsubscribed.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        // Unsubscription should be possible regardless of whether email features
        // are presently enabled, the mailing list is open, etc
        if (config('aldebaran.settings.email_features') && $subscriber->mailingList->is_open) {
            return redirect()->to('mailing-lists/'.$subscriber->mailing_list_id);
        } else {
            return redirect()->to('/');
        }
    }
}
