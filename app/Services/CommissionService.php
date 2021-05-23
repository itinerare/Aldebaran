<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionType;

class CommissionService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Commission Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of commission categories and types.
    |
    */

    /******************************************************************************
        COMMISSION CATEGORIES
    *******************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Commission\CommissionCategory|bool
     */
    public function createCommissionCategory($data, $user)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['is_active'])) $data['is_active'] = 0;

            $category = CommissionCategory::create($data);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param  \App\Models\Commission\CommissionCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Commission\CommissionCategory|bool
     */
    public function updateCommissionCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(CommissionCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            if(!isset($data['is_active'])) $data['is_active'] = 0;

            $category->update($data);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Delete a category.
     *
     * @param  \App\Models\Commission\CommissionCategory  $category
     * @return bool
     */
    public function deleteCommissionCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(CommissionType::where('category_id', $category->id)->exists()) throw new \Exception("A commission type with this category exists. Please change its category first.");

            $category->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortCommissionCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                CommissionCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        COMMISSION TYPES
    *******************************************************************************/

    /**
     * Creates a new commission type.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Commission\CommissionType
     */
    public function createCommissionType($data, $user)
    {
        DB::beginTransaction();

        try {
            if(!CommissionCategory::where('id', $data['category_id'])->exists()) throw new \Exception("The selected commission category is invalid.");

            $data = $this->populateData($data);
            $type = CommissionType::create($data);

            return $this->commitReturn($type);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a commission type.
     *
     * @param  \App\Models\Commission\CommissionType  $type
     * @param  array                                  $data
     * @param  \App\Models\User\User                  $user
     * @return bool|\App\Models\Commission\CommissionType
     */
    public function updateCommissionType($type, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(CommissionType::where('name', $data['name'])->where('id', '!=', $type->id)->where('category_id', $data['category_id'])->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['category_id']) && $data['category_id']) && !CommissionCategory::where('id', $data['category_id'])->exists()) throw new \Exception("The selected commission category is invalid.");

            $data = $this->populateData($data, $type);
            $type->update($data);

            return $this->commitReturn($type);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a commission type.
     *
     * @param  array                  $data
     * @param  \App\Models\Commission\CommissionType  $type
     * @return array
     */
    private function populateData($data, $type = null)
    {
        // Check toggles
        if(!isset($data['is_active'])) $data['is_active'] = 0;
        if(!isset($data['is_visible'])) $data['is_visible'] = 0;
        if(!isset($data['show_examples'])) $data['show_examples'] = 0;
        if(!isset($data['availability'])) $data['availability'] = 0;
        if(!isset($data['regenerate_key'])) $data['regenerate_key'] = 0;

        // Assemble and encode data
        $data['pricing']['type'] = $data['price_type'];
        switch($data['price_type']) {
            case 'flat':
                $data['pricing']['cost'] = $data['flat_cost'];
                break;
            case 'range':
                $data['pricing']['range'] = [
                    'min' => $data['cost_min'],
                    'max' => $data['cost_max']
                ];
                break;
            case 'min':
                $data['pricing']['cost'] = $data['minimum_cost'];
                break;
            case 'rate':
                $data['pricing']['cost'] = $data['rate'];
                break;
        }

        $data['data'] = [
            'pricing' => $data['pricing'],
            'extras' => isset($data['extras']) ? $data['extras'] : null,
            'show_examples' => $data['show_examples'],
            'tags' => isset($data['tags']) ? $data['tags'] : null
        ];
        $data['data'] = json_encode($data['data']);

        // Generate a key if the type is being created or if
        // it's set to be regenerated
        if(!$type || $data['regenerate_key']) $data['key'] = randomString(10);

        return $data;
    }

    /**
     * Deletes a commission type.
     *
     * @param  \App\Models\Commission\CommissionType  $type
     * @return bool
     */
    public function deleteCommissionType($type)
    {
        DB::beginTransaction();

        try {
            // Check first if there are commissions of this type
            if(DB::table('commissions')->where('commission_type', $type->id)->exists()) throw new \Exception("A commission of this type exists. Consider making the type unavailable instead.");

            $commission->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts type order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortCommissionType($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                CommissionType::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
