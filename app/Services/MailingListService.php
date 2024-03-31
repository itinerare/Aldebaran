<?php

namespace App\Services;

use App\Mail\MailListEntry;
use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailingListService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Mailing List Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of mailing lists and entries.
    |
    */

    /**
     * Creates a mailing list.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|MailingList
     */
    public function createMailingList($data, $user) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled.');
            }

            if (!isset($data['is_open'])) {
                $data['is_open'] = 0;
            }

            $mailingList = MailingList::create($data);

            return $this->commitReturn($mailingList);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a mailing list.
     *
     * @param MailingList           $mailingList
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|MailingList
     */
    public function updateMailingList($mailingList, $data, $user) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled.');
            }

            if (MailingList::where('name', $data['name'])->where('id', '!=', $mailingList->id)->exists()) {
                throw new \Exception('A mailing list with this name already exists.');
            }

            if (!isset($data['is_open'])) {
                $data['is_open'] = 0;
            }

            $mailingList->update($data);

            return $this->commitReturn($mailingList);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a mailing list.
     *
     * @param MailingList $mailingList
     *
     * @return bool
     */
    public function deleteMailingList($mailingList) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled.');
            }

            // Delete all subscribers and entries
            $mailingList->subscribers()->delete();
            $mailingList->entries()->delete();

            // Then delete the mailing list itself
            $mailingList->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        ENTRIES
    *******************************************************************************/

    /**
     * Creates an entry.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|MailingListEntry
     */
    public function createEntry($data, $user) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled.');
            }

            if (!isset($data['is_draft'])) {
                $data['is_draft'] = 0;
            }

            $entry = MailingListEntry::create($data);

            if (!$entry->is_draft) {
                $entry->update(['sent_at' => Carbon::now()]);
                foreach ($entry->mailingList->subscribers as $subscriber) {
                    Mail::to($subscriber->email)
                        ->queue(new MailListEntry($entry, $subscriber));
                    $subscriber->update(['last_entry_sent' => $entry->id]);
                }
            }

            return $this->commitReturn($entry);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an entry.
     *
     * @param MailingListEntry      $entry
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|MailingListEntry
     */
    public function updateEntry($entry, $data, $user) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled.');
            }

            if (!$entry->is_draft && isset($entry->sent_at)) {
                throw new \Exception('Sent entries cannot be edited.');
            }

            if (!isset($data['is_draft'])) {
                $data['is_draft'] = 0;
            }

            $entry->update($data);

            if (!$entry->is_draft) {
                $entry->update(['sent_at' => Carbon::now()]);
                foreach ($entry->mailingList->subscribers as $subscriber) {
                    Mail::to($subscriber->email)
                        ->queue(new MailListEntry($entry, $subscriber));
                    $subscriber->update(['last_entry_sent' => $entry->id]);
                }
            }

            return $this->commitReturn($entry);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an entry.
     *
     * @param MailingListEntry $entry
     *
     * @return bool
     */
    public function deleteEntry($entry) {
        DB::beginTransaction();

        try {
            if (!config('aldebaran.settings.email_features')) {
                throw new \Exception('Email features are currently disabled.');
            }

            $entry->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
