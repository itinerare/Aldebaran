<?php

namespace App\Services;

use DB;

class PageService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Text Page Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing of text pages. Creation is handled by the associated
    | config file and command, as pages must be manually incorporated into the site
    | in any case.
    |
    */

    /**
     * Updates a text page.
     *
     * @param \App\Models\TextPage  $page
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\TextPage|bool
     */
    public function updatePage($page, $data, $user)
    {
        DB::beginTransaction();

        try {
            $page->update($data);

            return $this->commitReturn($page);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
