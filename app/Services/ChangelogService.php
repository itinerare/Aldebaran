<?php

namespace App\Services;

use App\Models\Changelog;
use Illuminate\Support\Facades\DB;

class ChangelogService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Changelog Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of changelog entries.
    |
    */

    /**
     * Creates a changelog entry.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Changelog|bool
     */
    public function createLog($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $log = Changelog::create($data);

            return $this->commitReturn($log);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a changelog entry.
     *
     * @param \App\Models\Changelog $log
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Changelog|bool
     */
    public function updateLog($log, $data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $log->update($data);

            return $this->commitReturn($log);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a changelog entry.
     *
     * @param \App\Models\Changelog $log
     *
     * @return bool
     */
    public function deleteLog($log)
    {
        DB::beginTransaction();

        try {
            $log->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
