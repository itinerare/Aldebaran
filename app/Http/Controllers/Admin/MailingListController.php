<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailingList\MailingList;
use App\Services\MailingListService;
use Illuminate\Http\Request;

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
            'mailingLists' => MailingList::with(['entries', 'subscribers:id,mailing_list_id'])->orderBy('name')->paginate(20)->appends($request->query()),
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

        $mailingList = MailingList::find($id);
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
    public function postDeleteMailingList(Request $request, MailingListService $service, $id) {
        if ($id && $service->deleteMailingList(MailingList::find($id))) {
            flash('Mailing list deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/mailing-lists');
    }
}
