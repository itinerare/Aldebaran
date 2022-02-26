<?php

namespace App\Services;

use App\Facades\Settings;
use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionType;
use App\Models\TextPage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Commission Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of commission classes, categories, and types.
    |
    */

    /******************************************************************************
        COMMISSION CLASSES
    *******************************************************************************/

    /**
     * Create a class.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Commission\CommissionClass|bool
     */
    public function createCommissionClass($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }

            // Strip any tags from the provided name for safety and generate slug
            $data['name'] = strip_tags($data['name']);
            $data['slug'] = strtolower(str_replace(' ', '_', $data['name']));

            $class = CommissionClass::create($data);

            $this->processClassSettings($class, $data);

            return $this->commitReturn($class);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a class.
     *
     * @param \App\Models\Commission\CommissionClass $class
     * @param array                                  $data
     * @param \App\Models\User\User                  $user
     *
     * @return \App\Models\Commission\CommissionClass|bool
     */
    public function updateCommissionClass($class, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Strip any tags from the provided name for safety
            $data['name'] = strip_tags($data['name']);

            // More specific validation
            if (CommissionClass::where('name', $data['name'])->where('id', '!=', $class->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            // Set toggle and generate slug
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }
            $data['slug'] = strtolower(str_replace(' ', '_', $data['name']));

            // Save old information if change has occurred
            if ($data['slug'] != $class->slug) {
                $data['slug_old'] = $class->slug;
            }
            if (isset($class->data['pages'])) {
                $data['pages_old'] = $class->data['pages'];
            }

            // Process fields, site settings, and pages
            if (isset($data['field_key'])) {
                $data = $this->processFormFields($data);
            }
            $data = $this->processClassSettings($class, $data);

            $class->update($data);

            return $this->commitReturn($class);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a class.
     *
     * @param \App\Models\Commission\CommissionClass $class
     *
     * @return bool
     */
    public function deleteCommissionClass($class)
    {
        DB::beginTransaction();

        try {
            // Check first if the class is currently in use
            if (CommissionCategory::where('class_id', $class->id)->exists()) {
                throw new \Exception('A commission category with this class exists. Please change its class first.');
            }

            // Clean up automatically generated pages
            foreach (['tos', 'info'] as $page) {
                if (TextPage::where('key', $class->slug.$page)->first()) {
                    TextPage::where('key', $class->slug.$page)->first()->delete();
                }
            }

            // Clean up custom pages
            if (isset($class->data) && isset($class->data['pages'])) {
                foreach ($class->data['pages'] as $id=>$page) {
                    TextPage::where('id', $id)->first()->delete();
                }
            }

            // Clean up settings
            foreach (['comms_open', 'overall_slots', 'full', 'status'] as $setting) {
                if (DB::table('site_settings')->where('key', $class->slug.'_'.$setting)->exists()) {
                    DB::table('site_settings')->where('key', $class->slug.'_'.$setting)->delete();
                }
            }

            $class->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts class order.
     *
     * @param string $data
     *
     * @return bool
     */
    public function sortCommissionClass($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                CommissionClass::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        COMMISSION CATEGORIES
    *******************************************************************************/

    /**
     * Create a category.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Commission\CommissionCategory|bool
     */
    public function createCommissionCategory($data, $user)
    {
        DB::beginTransaction();

        try {
            $class = CommissionClass::find($data['class_id']);
            if (!$class) {
                throw new \Exception('Invalid commission class selected.');
            }

            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }

            $category = CommissionCategory::create($data);

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param \App\Models\Commission\CommissionCategory $category
     * @param array                                     $data
     * @param \App\Models\User\User                     $user
     *
     * @return \App\Models\Commission\CommissionCategory|bool
     */
    public function updateCommissionCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            $class = CommissionClass::find($data['class_id']);
            if (!$class) {
                throw new \Exception('Invalid commission class selected.');
            }

            // More specific validation
            if (CommissionCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }

            if (isset($data['field_key'])) {
                $data = $this->processFormFields($data);
            }
            if (!isset($data['include_class'])) {
                $data['data']['include']['class'] = 0;
            } else {
                $data['data']['include']['class'] = $data['include_class'];
            }

            $category->update($data);

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a category.
     *
     * @param \App\Models\Commission\CommissionCategory $category
     *
     * @return bool
     */
    public function deleteCommissionCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (CommissionType::where('category_id', $category->id)->exists()) {
                throw new \Exception('A commission type with this category exists. Please change its category first.');
            }

            $category->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param string $data
     *
     * @return bool
     */
    public function sortCommissionCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                CommissionCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
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
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Commission\CommissionType|bool
     */
    public function createCommissionType($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!CommissionCategory::where('id', $data['category_id'])->exists()) {
                throw new \Exception('The selected commission category is invalid.');
            }

            $data = $this->populateData($data);
            $type = CommissionType::create($data);

            return $this->commitReturn($type);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a commission type.
     *
     * @param \App\Models\Commission\CommissionType $type
     * @param array                                 $data
     * @param \App\Models\User\User                 $user
     *
     * @return \App\Models\Commission\CommissionType|bool
     */
    public function updateCommissionType($type, $data, $user)
    {
        DB::beginTransaction();

        try {
            if (CommissionType::where('name', $data['name'])->where('id', '!=', $type->id)->where('category_id', $data['category_id'])->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if ((isset($data['category_id']) && $data['category_id']) && !CommissionCategory::where('id', $data['category_id'])->exists()) {
                throw new \Exception('The selected commission category is invalid.');
            }

            if (isset($data['field_key'])) {
                $data = $this->processFormFields($data);
            }
            $data = $this->populateData($data, $type);

            $type->update($data);

            return $this->commitReturn($type);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a commission type.
     *
     * @param \App\Models\Commission\CommissionType $type
     *
     * @return bool
     */
    public function deleteCommissionType($type)
    {
        DB::beginTransaction();

        try {
            // Check first if there are commissions of this type
            if (DB::table('commissions')->where('commission_type', $type->id)->exists()) {
                throw new \Exception('A commission of this type exists. Consider making the type unavailable instead.');
            }

            $commission->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts type order.
     *
     * @param string $data
     *
     * @return bool
     */
    public function sortCommissionType($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                CommissionType::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes site settings and pages for a commission class.
     *
     * @param \App\Models\Commission\CommissionClass $class
     * @param array                                  $data
     *
     * @return array
     */
    private function processClassSettings($class, $data)
    {
        // Add and/or modify site settings
        // If the slug has been changed, check for existing settings and save their values
        if (isset($data['slug_old'])) {
            foreach ([$data['slug_old'].'_comms_open', $data['slug_old'].'_overall_slots', $data['slug_old'].'_full', $data['slug_old'].'_status'] as $setting) {
                if (DB::table('site_settings')->where('key', $setting)->exists()) {
                    $data['settings'][$setting] = Settings::get($setting);
                    DB::table('site_settings')->where('key', $setting)->delete();
                }
            }
        }

        // Create settings if necessary
        if (!DB::table('site_settings')->where('key', $class->slug.'_comms_open')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $class->slug.'_comms_open',
                    'value'       => isset($data['slug_old']) && isset($data['settings'][$data['slug_old'].'_comms_open']) ? $data['settings'][$data['slug_old'].'_comms_open'] : 0,
                    'description' => 'Whether or not commissions are open.',
                ],
            ]);
        }

        if (!DB::table('site_settings')->where('key', $class->slug.'_overall_slots')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $class->slug.'_overall_slots',
                    'value'       => isset($data['slug_old']) && isset($data['settings'][$data['slug_old'].'_overall_slots']) ? $data['settings'][$data['slug_old'].'_overall_slots'] : 0,
                    'description' => 'Overall number of availabile commission slots. Set to 0 to disable limits.',
                ],
            ]);
        }

        if (!DB::table('site_settings')->where('key', $class->slug.'_status')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $class->slug.'_status',
                    'value'       => isset($data['slug_old']) && isset($data['settings'][$data['slug_old'].'_status']) ? $data['settings'][$data['slug_old'].'_status'] : 0,
                    'description' => 'Optional; a short message about commission status. Set to 0 to unset/leave blank.',
                ],
            ]);
        }

        if (!DB::table('site_settings')->where('key', $class->slug.'_full')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $class->slug.'_full',
                    'value'       => isset($data['slug_old']) && isset($data['settings'][$data['slug_old'].'_full']) ? $data['settings'][$data['slug_old'].'_full'] : 'Thank you for your interest in commissioning me, and I hope you consider submitting a request when next I open commissions!',
                    'description' => 'A short message used when auto-declining commissions over a slot limit.',
                ],
            ]);
        }

        // Add and/or modify text pages
        $pages = [
            $class->slug.'tos' => [
                'name' => $class->name.' Commission Terms of Service',
                'text' => '<p>'.$class->name.' commssion terms of service go here.</p>',
                'flag' => 'tos',
            ],
            $class->slug.'info' => [
                'name' => $class->name.' Commission Info',
                'text' => '<p>'.$class->name.' commssion info goes here.</p>',
                'flag' => 'info',
            ],
        ];

        // Check that entered page keys do not already have associated pages
        if (isset($data['page_key'])) {
            foreach ($data['page_key'] as $key=>$pageKey) {
                if (TextPage::where('key', $pageKey)->exists() && $data['page_id'][$key] == null) {
                    throw new \Exception('One or more page keys have already been taken.');
                }
            }
        }

        if (isset($data['page_key'])) {
            foreach ($data['page_key'] as $key=>$pageKey) {
                if ($data['page_id'][$key] == null) {
                    $pages = $pages + [$pageKey => [
                'name' => $data['page_title'][$key],
                'text' => '<p>'.$class->name.' commssion info goes here.</p>',
                'flag' => 'custom',
            ]];
                }
            }
        }

        // If the slug has been changed, check for existing pages and save their content
        if (isset($data['slug_old'])) {
            foreach ($pages as $pageInfo) {
                $page = TextPage::where('key', $data['slug_old'].$pageInfo['flag'])->first();
                if ($page) {
                    $data['pages'][$pageInfo['flag']] = $page->text;
                    $page->delete();
                }
            }
        }

        // Update and/or remove old pages
        if (isset($data['pages_old'])) {
            foreach ($data['pages_old'] as $id=>$oldPage) {
                $page = TextPage::find($id);
                // Check to see if the page is still among the results/should still exist
                if (isset($data['page_id'])) {
                    foreach ($data['page_id'] as $pageId) {
                        if ($pageId == $id) {
                            $pageExists[$page->id] = true;
                        }
                    }
                }

                // If so, update it if necessary
                if (isset($pageExists[$page->id]) && $pageExists[$id]) {
                    foreach ($data['page_id'] as $key=>$id) {
                        if ($id == $page->id) {
                            if (isset($data['page_key'][$key]) && $data['page_key'][$key] != $page->key) {
                                $page->key = $data['page_key'][$key];
                            }
                            if (isset($data['page_title'][$key]) && $data['page_title'][$key] != $page->key) {
                                $page->name = $data['page_title'][$key];
                            }
                            $page->save();
                        }
                    }
                } else {
                    $page->delete();
                }
            }
        }

        // Create pages if necessary
        foreach ($pages as $key=>$page) {
            if (!DB::table('text_pages')->where('key', $key)->exists()) {
                DB::table('text_pages')->insert([
                    [
                        'key'        => $key,
                        'name'       => $page['name'],
                        'text'       => isset($data['slug_old']) && isset($data['pages'][$page['flag']]) ? $data['pages'][$page['flag']] : $page['text'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],

                ]);
            }
        }

        if (isset($data['page_key'])) {
            foreach ($data['page_key'] as $key=>$pageKey) {
                $data['data']['pages'][TextPage::where('key', $pageKey)->first()->id] = [
                    'key'   => $data['page_key'][$key],
                    'title' => $data['page_title'][$key],
                ];
            }
        }

        return $data;
    }

    /**
     * Processes form field information.
     *
     * @param array $data
     *
     * @return array
     */
    private function processFormFields($data)
    {
        foreach ($data['field_key'] as $key=>$fieldKey) {
            if (isset($data['field_choices'][$key])) {
                $data['field_choices'][$key] = explode(',', $data['field_choices'][$key]);
            }

            $data['data']['fields'][$fieldKey] = [
                'label'   => $data['field_label'][$key],
                'type'    => $data['field_type'][$key],
                'rules'   => isset($data['field_rules'][$key]) ? $data['field_rules'][$key] : null,
                'choices' => isset($data['field_choices'][$key]) ? $data['field_choices'][$key] : null,
                'value'   => isset($data['field_value'][$key]) ? $data['field_value'][$key] : null,
                'help'    => isset($data['field_help'][$key]) ? $data['field_help'][$key] : null,
            ];
        }

        return $data;
    }

    /**
     * Processes user input for creating/updating a commission type.
     *
     * @param array                                 $data
     * @param \App\Models\Commission\CommissionType $type
     *
     * @return array
     */
    private function populateData($data, $type = null)
    {
        // Check toggles
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (!isset($data['show_examples'])) {
            $data['show_examples'] = 0;
        }
        if (!isset($data['availability'])) {
            $data['availability'] = 0;
        }
        if (!isset($data['regenerate_key'])) {
            $data['regenerate_key'] = 0;
        }

        // Check form include toggles
        if (!isset($data['include_class'])) {
            $data['data']['include']['class'] = 0;
        } else {
            $data['data']['include']['class'] = 1;
        }
        if (!isset($data['include_category'])) {
            $data['data']['include']['category'] = 0;
        } else {
            $data['data']['include']['category'] = 1;
        }

        // Assemble and encode data
        $data['pricing']['type'] = $data['price_type'];
        switch ($data['price_type']) {
            case 'flat':
                $data['pricing']['cost'] = $data['flat_cost'];
                break;
            case 'range':
                $data['pricing']['range'] = [
                    'min' => $data['cost_min'],
                    'max' => $data['cost_max'],
                ];
                break;
            case 'min':
                $data['pricing']['cost'] = $data['minimum_cost'];
                break;
            case 'rate':
                $data['pricing']['cost'] = $data['rate'];
                break;
        }

        $data['data']['pricing'] = $data['pricing'];
        $data['data']['extras'] = isset($data['extras']) ? $data['extras'] : null;
        $data['data']['show_examples'] = $data['show_examples'];
        $data['data']['tags'] = isset($data['tags']) ? $data['tags'] : null;

        // Generate a key if the type is being created or if
        // it's set to be regenerated
        if (!$type || $data['regenerate_key']) {
            $data['key'] = randomString(10);
        }

        return $data;
    }
}
