<?php

namespace App\Services;

use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListEntry;
use Illuminate\Support\Facades\DB;

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
     * @return \App\Models\MailingList\MailingList|bool
     */
    public function createMailingList($data, $user) {
        DB::beginTransaction();

        try {
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
     * @param \App\Models\MailingList\MailingList $mailingList
     * @param array                               $data
     * @param \App\Models\User\User               $user
     *
     * @return \App\Models\MailingList\MailingList|bool
     */
    public function updateMailingList($mailingList, $data, $user) {
        DB::beginTransaction();

        try {
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
     * @param \App\Models\MailingList\MailingList $mailingList
     *
     * @return bool
     */
    public function deleteMailingList($mailingList) {
        DB::beginTransaction();

        try {
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
     * @return \App\Models\MailingList\MailingListEntry|bool
     */
    public function createEntry($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['is_draft'])) {
                $data['is_draft'] = 0;
            }

            $entry = MailingListEntry::create($data);

            return $this->commitReturn($entry);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an entry.
     *
     * @param \App\Models\MailingList\MailingListEntry $entry
     * @param array                                    $data
     * @param \App\Models\User\User                    $user
     *
     * @return \App\Models\MailingList\MailingListEntry|bool
     */
    public function updateEntry($entry, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$entry->is_draft && isset($entry->sent_at)) {
                throw new \Exception('Sent entries cannot be edited.');
            }

            if (!isset($data['is_draft'])) {
                $data['is_draft'] = 0;
            }

            $entry->update($data);

            return $this->commitReturn($entry);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an entry.
     *
     * @param \App\Models\MailingList\MailingListEntry $entry
     *
     * @return bool
     */
    public function deleteEntry($entry) {
        DB::beginTransaction();

        try {
            $entry->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles entry sending.
     *
     * @param \App\Models\MailingList\MailingListEntry $entry
     *
     * @return \App\Models\MailingList\MailingListEntry|bool
     */
    private function sendEntry($entry) {
        DB::beginTransaction();

        try {
            //

            return $this->commitReturn($entry);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
