<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionClass;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Tag;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\ValidationRules\Rules\Delimited;
use Stripe\StripeClient;

class CommissionController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Commission Data Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of commission data (classes, categories, and types).
    |
    */

    /******************************************************************************
        COMMISSION CLASSES
    *******************************************************************************/

    /**
     * Shows the commission class index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCommissionClassIndex() {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        return view('admin.commissions.commission_classes', [
            'classes' => CommissionClass::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create commission class page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCommissionClass() {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        return view('admin.commissions.create_edit_commission_class', [
            'class'      => new CommissionClass,
            'fieldTypes' => ['text' => 'Text', 'textarea' => 'Textbox', 'number' => 'Number', 'checkbox' => 'Checkbox/Toggle', 'choice' => 'Choose One', 'multiple' => 'Choose Multiple'],
            'taxCode'    => null,
        ]);
    }

    /**
     * Shows the edit commission class page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCommissionClass($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::find($id);
        if (!$class) {
            abort(404);
        }

        if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') && isset($class->invoice_data['product_tax_code'])) {
            // Retrieve information for the current tax code, for convenience
            $taxCode = (new StripeClient(config('aldebaran.commissions.payment_processors.stripe.integration.secret_key')))->taxCodes->retrieve($class->invoice_data['product_tax_code']);
        }

        return view('admin.commissions.create_edit_commission_class', [
            'class'      => $class,
            'fieldTypes' => ['text' => 'Text', 'textarea' => 'Textbox', 'number' => 'Number', 'checkbox' => 'Checkbox/Toggle', 'choice' => 'Choose One', 'multiple' => 'Choose Multiple'],
            'taxCode'    => $taxCode ?? null,
        ]);
    }

    /**
     * Creates or edits an commission class.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCommissionClass(Request $request, CommissionService $service, $id = null) {
        $id ? $request->validate(CommissionClass::$updateRules) : $request->validate(CommissionClass::$createRules);
        $data = $request->only([
            'name', 'is_active', 'page_id', 'page_title', 'page_key',
            'field_key', 'field_type', 'field_label', 'field_rules', 'field_choices', 'field_value', 'field_help',
            'product_name', 'product_description', 'product_tax_code',
        ]);
        // Fancy validation for field choices and rules
        if ($id) {
            if (isset($data['field_choices'])) {
                foreach ($data['field_choices'] as $choices) {
                    if ($choices != null) {
                        $validator = Validator::make(['choices' => $choices], ['choices' => (new Delimited('string'))->separatedBy(',')->min(2)->max(5)]);
                        if ($validator->fails()) {
                            flash($validator->errors()->first())->error();

                            return redirect()->back();
                        }
                    }
                }
            }
            if (isset($data['field_rules'])) {
                foreach ($data['field_rules'] as $rules) {
                    if ($rules != null) {
                        $validator = Validator::make(['rules' => $rules], ['rules' => (new Delimited('string'))->separatedBy('|')]);
                        if ($validator->fails()) {
                            flash($validator->errors()->first())->error();

                            return redirect()->back();
                        }
                    }
                }
            }
        }

        if ($id && $service->updateCommissionClass(CommissionClass::find($id), $data, $request->user())) {
            flash('Class updated successfully.')->success();
        } elseif (!$id && $class = $service->createCommissionClass($data, $request->user())) {
            flash('Class created successfully.')->success();

            return redirect()->to('admin/data/commission-classes/edit/'.$class->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the commission class deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCommissionClass($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $class = CommissionClass::find($id);

        return view('admin.commissions._delete_commission_class', [
            'class' => $class,
        ]);
    }

    /**
     * Deletes an commission class.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCommissionClass(Request $request, CommissionService $service, $id) {
        if ($id && $service->deleteCommissionClass(CommissionClass::find($id))) {
            flash('Class deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/commission-classes');
    }

    /**
     * Sorts commission classes.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCommissionClass(Request $request, CommissionService $service) {
        if ($service->sortCommissionClass($request->get('sort'))) {
            flash('Class order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /******************************************************************************
        COMMISSION CATEGORIES
    *******************************************************************************/

    /**
     * Shows the commission category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        return view('admin.commissions.commission_categories', [
            'categories' => CommissionCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create commission category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCommissionCategory() {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        return view('admin.commissions.create_edit_commission_category', [
            'category'   => new CommissionCategory,
            'classes'    => CommissionClass::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'fieldTypes' => ['text' => 'Text', 'textarea' => 'Textbox', 'number' => 'Number', 'checkbox' => 'Checkbox/Toggle', 'choice' => 'Choose One', 'multiple' => 'Choose Multiple'],
            'taxCode'    => null,
        ]);
    }

    /**
     * Shows the edit commission category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCommissionCategory($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $category = CommissionCategory::find($id);
        if (!$category) {
            abort(404);
        }

        if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') && (isset($category->invoice_data['product_tax_code']) || isset($category->parentInvoiceData['product_tax_code']))) {
            // Retrieve information for the current tax code, for convenience
            $taxCode = (new StripeClient(config('aldebaran.commissions.payment_processors.stripe.integration.secret_key')))->taxCodes->retrieve(
                $category->invoice_data['product_tax_code'] ?? $category->parentInvoiceData['product_tax_code']
            );
        }

        return view('admin.commissions.create_edit_commission_category', [
            'category'   => $category,
            'classes'    => CommissionClass::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'fieldTypes' => ['text' => 'Text', 'textarea' => 'Textbox', 'number' => 'Number', 'checkbox' => 'Checkbox/Toggle', 'choice' => 'Choose One', 'multiple' => 'Choose Multiple'],
            'taxCode'    => $taxCode ?? null,
        ]);
    }

    /**
     * Creates or edits an commission category.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCommissionCategory(Request $request, CommissionService $service, $id = null) {
        $id ? $request->validate(CommissionCategory::$updateRules) : $request->validate(CommissionCategory::$createRules);
        $data = $request->only([
            'name', 'class_id', 'is_active',
            'field_key', 'field_type', 'field_label', 'field_rules', 'field_choices', 'field_value', 'field_help', 'include_class',
            'product_name', 'product_description', 'product_tax_code', 'unset_product_info',
        ]);
        // Fancy validation for field choices and rules
        if ($id) {
            if (isset($data['field_choices'])) {
                foreach ($data['field_choices'] as $choices) {
                    if ($choices != null) {
                        $validator = Validator::make(['choices' => $choices], ['choices' => (new Delimited('string'))->separatedBy(',')->min(2)->max(5)]);
                        if ($validator->fails()) {
                            flash($validator->errors()->first())->error();

                            return redirect()->back();
                        }
                    }
                }
            }
            if (isset($data['field_rules'])) {
                foreach ($data['field_rules'] as $rules) {
                    if ($rules != null) {
                        $validator = Validator::make(['rules' => $rules], ['rules' => (new Delimited('string'))->separatedBy('|')]);
                        if ($validator->fails()) {
                            flash($validator->errors()->first())->error();

                            return redirect()->back();
                        }
                    }
                }
            }
        }

        if ($id && $service->updateCommissionCategory(CommissionCategory::find($id), $data, $request->user())) {
            flash('Category updated successfully.')->success();
        } elseif (!$id && $category = $service->createCommissionCategory($data, $request->user())) {
            flash('Category created successfully.')->success();

            return redirect()->to('admin/data/commission-categories/edit/'.$category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the commission category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCommissionCategory($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $category = CommissionCategory::find($id);

        return view('admin.commissions._delete_commission_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an commission category.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCommissionCategory(Request $request, CommissionService $service, $id) {
        if ($id && $service->deleteCommissionCategory(CommissionCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/commission-categories');
    }

    /**
     * Sorts commission categories.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCommissionCategory(Request $request, CommissionService $service) {
        if ($service->sortCommissionCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /******************************************************************************
        COMMISSION TYPES
    *******************************************************************************/

    /**
     * Shows the commission type index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCommissionTypeIndex(Request $request) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $query = CommissionType::query()->with(['category:id,name,class_id', 'commissions:id,commission_type,status']);
        $data = $request->only(['category_id', 'name']);
        if (isset($data['category_id']) && $data['category_id'] != 'none') {
            $query->where('category_id', $data['category_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.commissions.commission_types', [
            'types'      => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + CommissionCategory::orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create commission type page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCommissionType() {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }

        return view('admin.commissions.create_edit_commission_type', [
            'type'       => new CommissionType,
            'categories' => CommissionCategory::orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray(),
            'tags'       => Tag::orderBy('name')->pluck('name', 'id')->toArray(),
            'fieldTypes' => ['text' => 'Text', 'textarea' => 'Textbox', 'number' => 'Number', 'checkbox' => 'Checkbox/Toggle', 'choice' => 'Choose One', 'multiple' => 'Choose Multiple'],
            'taxCode'    => null,
        ]);
    }

    /**
     * Shows the edit commission type page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCommissionType($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $commissionType = CommissionType::find($id);
        if (!$commissionType) {
            abort(404);
        }

        if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') && (isset($commissionType->invoice_data['product_tax_code']) || isset($commissionType->parentInvoiceData['product_tax_code']))) {
            // Retrieve information for the current tax code, for convenience
            $taxCode = (new StripeClient(config('aldebaran.commissions.payment_processors.stripe.integration.secret_key')))->taxCodes->retrieve(
                $commissionType->invoice_data['product_tax_code'] ?? $commissionType->parentInvoiceData['product_tax_code']
            );
        }

        return view('admin.commissions.create_edit_commission_type', [
            'type'       => $commissionType,
            'categories' => CommissionCategory::orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray(),
            'tags'       => Tag::orderBy('name')->pluck('name', 'id')->toArray(),
            'fieldTypes' => ['text' => 'Text', 'textarea' => 'Textbox', 'number' => 'Number', 'checkbox' => 'Checkbox/Toggle', 'choice' => 'Choose One', 'multiple' => 'Choose Multiple'],
            'taxCode'    => $taxCode ?? null,
        ]);
    }

    /**
     * Creates or edits a commission type.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCommissionType(Request $request, CommissionService $service, $id = null) {
        $request->validate(CommissionType::$rules);
        $data = $request->only([
            'category_id', 'name', 'description', 'is_active', 'is_visible', 'availability',
            'price_type', 'flat_cost', 'cost_min', 'cost_max', 'minimum_cost', 'rate',
            'extras', 'tags', 'show_examples', 'regenerate_key',
            'field_key', 'field_type', 'field_label', 'field_rules', 'field_choices', 'field_value', 'field_help', 'include_class', 'include_category',
            'product_name', 'product_description', 'product_tax_code', 'unset_product_info',
        ]);
        // Fancy validation for field choices and rules
        if ($id) {
            if (isset($data['field_choices'])) {
                foreach ($data['field_choices'] as $choices) {
                    if ($choices != null) {
                        $validator = Validator::make(['choices' => $choices], ['choices' => (new Delimited('string'))->separatedBy(',')->min(2)->max(5)]);
                        if ($validator->fails()) {
                            flash($validator->errors()->first())->error();

                            return redirect()->back();
                        }
                    }
                }
            }
            if (isset($data['field_rules'])) {
                foreach ($data['field_rules'] as $rules) {
                    if ($rules != null) {
                        $validator = Validator::make(['rules' => $rules], ['rules' => (new Delimited('string'))->separatedBy('|')]);
                        if ($validator->fails()) {
                            flash($validator->errors()->first())->error();

                            return redirect()->back();
                        }
                    }
                }
            }
        }

        if ($id && $service->updateCommissionType(CommissionType::find($id), $data, $request->user())) {
            flash('Commission type updated successfully.')->success();
        } elseif (!$id && $type = $service->createCommissionType($data, $request->user())) {
            flash('Commission type created successfully.')->success();

            return redirect()->to('admin/data/commission-types/edit/'.$type->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the commission type deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCommissionType($id) {
        if (!config('aldebaran.commissions.enabled')) {
            abort(404);
        }
        $commissionType = CommissionType::find($id);

        return view('admin.commissions._delete_commission_type', [
            'type' => $commissionType,
        ]);
    }

    /**
     * Deletes a commission type.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCommissionType(Request $request, CommissionService $service, $id) {
        if ($id && $service->deleteCommissionType(CommissionType::find($id))) {
            flash('Commission type deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->to('admin/data/commission-types');
    }

    /**
     * Sorts commission types.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCommissionType(Request $request, CommissionService $service) {
        if ($service->sortCommissionType($request->get('sort'))) {
            flash('Type order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                $service->addError($error);
            }
        }

        return redirect()->back();
    }
}
