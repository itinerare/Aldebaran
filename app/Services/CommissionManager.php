<?php namespace App\Services;

use App\Services\Service;

use DB;
use Settings;

use App\Models\Commission\CommissionType;
use App\Models\Commission\Commission;
use App\Models\Commission\CommissionPiece;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionerIp;

use App\Models\Gallery\Piece;

class CommissionManager extends Service
{
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
     * @param  array              $data
     * @param  bool               $manual
     * @return bool|\App\Models\Commission\Commission
     */
    public function createCommission($data, $manual = false)
    {
        DB::beginTransaction();

        try {
            // Verify the type and, if necessary, key
            $type = CommissionType::where('id', $data['type'])->first();
            if(!$type) throw new \Exception("The selected commission type is invalid.");
            if(!$manual) {
                // Check that commissions are open for this type and for the global type
                if(!Settings::get($type->category->type.'_comms_open')) throw new \Exception('Commissions are not open.');
                if(!$type->category->is_active) throw new \Exception('Commissions are not open for this category.');
                if(!$type->is_active) throw new \Exception('Commissions are not open for this type.');
                // If the commission type is currently hidden, check for the presence of
                // the key, and if so, check it
                if(!$type->is_visible && (!isset($data['key']) || $type->key != $data['key'])) throw new \Exception('Commissions are not open for this type.');
            }

            if(isset($data['commissioner_id'])) {
                $commissioner = Commissioner::where('id', $data['commissioner_id'])->first();
                if(!$commissioner) throw new \Exception('Invalid commissioner selected.');
            }
            else $commissioner = $this->processCommissioner($data, $manual ? false : true);

            // Collect and encode form responses related to the commission itself
            foreach(['references', 'details', 'background'] as $field) {
                if(isset($data[$field])) $data['description'][$field] = strip_tags($data[$field]);
                else $data['description'][$field] = null;
            }
            foreach(['shading', 'style'] as $field) {
                if(isset($data[$field])) $data['data'][$field] = strip_tags($data[$field]);
                else $data['data'][$field] = null;
            }

            $commission = Commission::create([
                'commissioner_id' => $commissioner->id,
                'commission_type' => $type->id,
                'status' => 'Pending',
                'data' => json_encode($data['data']),
                'description' => json_encode($data['description'])
            ]);

            // Now that the commission has an ID, assign it a key incorporating it
            // This ensures that even in the very odd case of a duplicate key,
            // conflicts should not arise
            $commission->update(['key' => $commission->id.'_'.randomString(15)]);

            return $this->commitReturn($commission);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating commissioner data.
     *
     * @param  array                      $data
     * @param  bool                       $processIp
     * @return array
     */
    private function processCommissioner($data, $processIp = true)
    {
        // Attempt to fetch commissioner, first by email, then by IP as a fallback
        // Failing these, create a new commissioner
        $commissioner = Commissioner::where('email', $data['email'])->first();
        if(!$commissioner && $processIp) {
            // Fetch by IP to check for ban
            $ipCommissioner = CommissionerIp::where('ip', $data['ip'])->first() ? CommissionerIp::where('ip', $data['ip'])->first()->commissioner : null;
            if($ipCommissioner && $ipCommissioner->id)
                if($ipCommissioner->is_banned) throw new \Exception('Unable to submit commission request. You are banned.');
        }

        // Update existing commissioner information
        if($commissioner && $commissioner->id) {
            // Check for ban
            if($commissioner->is_banned) throw new \Exception('Unable to submit commission request. You are banned.');

            $commissioner->update([
                'email' => (isset($data['email']) && $data['email'] != $commissioner->email ? $data['email'] : $commissioner->email),
                'name' => (isset($data['name']) && $data['name'] != $commissioner->getRawOriginal('name') ? $data['name'] : $commissioner->name),
                'contact' => (isset($data['contact']) && $data['contact'] != $commissioner->contact ? strip_tags($data['contact']) : $commissioner->contact),
                'paypal' => (isset($data['paypal']) && $data['paypal'] != $commissioner->paypal ? $data['paypal'] : $commissioner->paypal),
            ]);
        }
        // Create commissioner information
        else {
            $commissioner = Commissioner::create([
                'name' => isset($data['name']) ? $data['name'] : null,
                'email' => $data['email'],
                'contact' => strip_tags($data['contact']),
                'paypal' => isset($data['paypal']) ? $data['paypal'] : $data['email']
            ]);
        }

        // If commissioner and/or IP are new, process IP data
        if($processIp && !$commissioner->ips->where('ip', $data['ip'])->first()) {
            CommissionerIp::create([
                'commissioner_id' => $commissioner->id,
                'ip' => $data['ip']
            ]);
        }

        return $commissioner;
    }

    /**
     * Accepts a commission.
     *
     * @param  int                    $id
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function acceptCommission($id, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if(!$user) throw new \Exception('Invalid user.');
            // Check that the commission exists and is pending
            $commission = Commission::where('id', $id)->where('status', 'Pending')->first();
            if(!$commission) throw new \Exception('Invalid commission selected.');
            // Check that this commission will not be in excess of any slot limitations
            if(Settings::get('overall_'.$commission->commType->category->type.'_slots') > 0 || $commission->commType->slots != null) {
                if($commission->commType->getSlots($commission->commType->category->type) == 0) throw new \Exception('There are no overall slots of this commission type remaining.');
                if($commission->commType->availability > 0 && $commission->commType->currentSlots == 0) throw new \Exception('This commission  type\'s slots are full.');
            }

            // Update the commission status and comments
            $commission->update([
                'status' => 'Accepted',
                'comments' => isset($data['comments']) ? $data['comments'] : null
            ]);

            // If this is the last available commission slot overall or for this type,
            // automatically decline any remaining pending requests
            if(Settings::get('overall_'.$commission->commType->category->type.'_slots') > 0 || $commission->commType->slots != null) {
                // Overall slots filled
                if($commission->commType->getSlots($commission->commType->category->type) == 0) {
                    $type = $commission->commType->category->type;
                    Commission::type($type)->where('status', 'Pending')->update(['status' => 'Declined', 'comments' => '<p>Sorry, all slots have been filled! Thank you for your interest in commissioning me, and I hope you consider submitting a request when next I open commissions!</p>']);
                }
                // Type slots filled
                elseif($commission->commType->availability > 0 && $commission->commType->currentSlots == 0) {
                    dd($commission->commType->currentSlots);
                    Commission::where('commission_type', $commission->commType)->where('status', 'Pending')->update(['status' => 'Declined', 'comments' => '<p>Sorry, all slots for this commission type have been filled! Thank you for your interest in commissioning me, and I hope you consider submitting a request when next I open commissions!</p>']);
                }
            }

            return $this->commitReturn($commission);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a commission.
     *
     * @param  int                    $id
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function updateCommission($id, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if(!$user) throw new \Exception('Invalid user.');
            // Check that the commission exists and is accepted
            $commission = Commission::where('id', $id)->where('status', 'Accepted')->first();
            if(!$commission) throw new \Exception('Invalid commission selected.');

            // Process data as necessary
            if(isset($data['pieces'])) {
                // Check that all selected pieces exist
                $pieces = Piece::whereIn('id', $data['pieces'])->get();
                if(count($data['pieces']) != $pieces->count()) throw new \Exception('One or more of the selected pieces is invalid.');

                // Clear old pieces
                CommissionPiece::where('commission_id', $commission->id)->delete();

                // Create commission piece record for each piece
                foreach($pieces as $piece)
                    CommissionPiece::create([
                        'commission_id' => $commission->id,
                        'piece_id' => $piece->id
                    ]);
            }

            if(!isset($data['paid_status'])) $data['paid_status'] = 0;

            // Update the commission
            $commission->update($data);

            return $this->commitReturn($commission);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Marks a commission complete.
     *
     * @param  int                    $id
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function completeCommission($id, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if(!$user) throw new \Exception('Invalid user.');
            // Check that the commission exists and is accepted
            $commission = Commission::where('id', $id)->where('status', 'Accepted')->first();
            if(!$commission) throw new \Exception('Invalid commission selected.');

            // Update the commission status and comments
            $commission->update([
                'status' => 'Complete',
                'progress' => 'Finished',
                'comments' => isset($data['comments']) ? $data['comments'] : null
            ]);

            return $this->commitReturn($commission);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Declines a commission.
     *
     * @param  int                    $id
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function declineCommission($id, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if(!$user) throw new \Exception('Invalid user.');
            // Check that the commission exists and is pending
            $commission = Commission::where('id', $id)->whereIn('status', ['Pending', 'Accepted'])->first();
            if(!$commission) throw new \Exception('Invalid commission selected.');

            // Update the commission status and comments
            $commission->update([
                'status' => 'Declined',
                'comments' => isset($data['comments']) ? $data['comments'] : null
            ]);

            return $this->commitReturn($commission);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Bans a commissioner, and declines all current commission requests from them.
     *
     * @param  int                    $id
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function banCommissioner($id, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that there is a user
            if(!$user) throw new \Exception('Invalid user.');
            // Fetch commission so as to fetch commissioner
            $commission = Commission::where('id', $id)->whereIn('status', ['Pending', 'Accepted'])->first();
            if(!$commission) throw new \Exception('Invalid commission selected.');
            $commissioner = Commissioner::where('id', $commission->commissioner_id)->first();
            if(!$commissioner) throw new \Exception('Invalid commissioner selected.');

            // Mark the commissioner as banned,
            $commissioner->update(['is_banned' => 1]);
            // and decline all current commission requests from them
            Commission::where('commissioner_id', $commissioner->id)->whereIn('status', ['Pending', 'Accepted'])->update(['status' => 'Declined', 'comments' => '<p>Automatically declined due to ban.</p>']);

            return $this->commitReturn($commission);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}