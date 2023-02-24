<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListSubscriber;
use App\Services\CommissionManager;
use App\Services\MailingListManager;
use App\Services\MailingListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailingListController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Mailing List Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of mailing lists and entries.
    |
    */

    /**
     * Shows the mailing list index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMailingListIndex(Request $request) {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        return view('admin.mailing_lists.index', [
            'mailingLists' => MailingList::with(['subscribers', 'entries'])->orderBy('name')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create mailing list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateMailingList() {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        return view('admin.mailing_lists.create_edit_list', [
            'mailingList' => new MailingList,
        ]);
    }

    /**
     * Shows the edit mailing list page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditMailingList($id) {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        $mailingList = MailingList::with(['subscribers', 'entries'])->find($id);
        if (!$mailingList) {
            abort(404);
        }

        return view('admin.mailing_lists.create_edit_list', [
            'mailingList' => $mailingList,
        ]);
    }

    /**
     * Creates or edits a mailing list.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditMailingList(Request $request, MailingListService $service, $id = null) {
        $id ? $request->validate(MailingList::$updateRules) : $request->validate(MailingList::$createRules);
        $data = $request->only([
            'name', 'description', 'is_open',
        ]);
        if ($id && $service->updateMailingList(MailingList::find($id), $data, $request->user())) {
            flash('Mailing list updated successfully.')->success();
        } elseif (!$id && $mailingList = $service->createMailingList($data, $request->user())) {
            flash('Mailing list created successfully.')->success();

            return redirect()->to('admin/mailing-lists/edit/'.$mailingList->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the mailing list deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteMailingList($id) {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        $mailingList = MailingList::find($id);

        return view('admin.mailing_lists._delete_list', [
            'mailingList' => $mailingList,
        ]);
    }

    /**
     * Deletes a mailing list.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteMailingList(MailingListService $service, $id) {
        if ($id && $service->deleteMailingList(MailingList::find($id))) {
            flash('Mailing list deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/mailing-lists');
    }

    /******************************************************************************
        SUBSCRIBERS
    *******************************************************************************/

    /**
     * Gets the kick subscriber modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getKickSubscriber($id) {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        $subscriber = MailingListSubscriber::find($id);

        return view('admin.mailing_lists._kick_subscriber', [
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Kicks a subscriber.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postKickSubscriber(MailingListManager $service, $id) {
        $subscriber = MailingListSubscriber::with('mailingList')->find($id);
        $mailingList = $subscriber->mailingList;

        if ($id && $service->kickSubscriber($subscriber)) {
            flash('Subscriber force unsubscribed successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/mailing-lists/edit/'.$mailingList->id);
    }

    /**
     * Gets the ban subscriber modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBanSubscriber($id) {
        if (!config('aldebaran.settings.email_features')) {
            abort(404);
        }

        $subscriber = MailingListSubscriber::find($id);

        return view('admin.mailing_lists._ban_subscriber', [
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Bans a subscriber.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postBanSubscriber(CommissionManager $service, $id) {
        if ($id && $service->banCommissioner(MailingListSubscriber::find($id)->email, [], Auth::user())) {
            flash('Subscriber banned successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/mailing-lists');
    }
}
