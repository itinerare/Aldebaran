<?php

namespace App\Http\Controllers\Admin\Data;

use Auth;
use Config;

use App\Models\Commission\CommissionCategory;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Tag;
use App\Services\CommissionService;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Commission Data Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of commission data (categories and types).
    |
    */

    /******************************************************************************
        COMMISSION CATEGORIES
    *******************************************************************************/

    /**
     * Shows the commission category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.commissions.commission_categories', [
            'categories' => CommissionCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create commission category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCommissionCategory()
    {
        // Fetch types from config and format for dropdown
        $types = Config::get('itinerare.comm_types');
        foreach($types as $key=>$type) $types[$key] = ucfirst($key);

        return view('admin.commissions.create_edit_commission_category', [
            'category' => new CommissionCategory,
            'types' => $types
        ]);
    }

    /**
     * Shows the edit commission category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCommissionCategory($id)
    {
        $category = CommissionCategory::find($id);
        if(!$category) abort(404);

        // Fetch types from config and format for dropdown
        $types = Config::get('itinerare.comm_types');
        foreach($types as $key=>$type) $types[$key] = ucfirst($key);

        return view('admin.commissions.create_edit_commission_category', [
            'category' => $category,
            'types' => $types
        ]);
    }

    /**
     * Creates or edits an commission category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\CommissionService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCommissionCategory(Request $request, CommissionService $service, $id = null)
    {
        $id ? $request->validate(CommissionCategory::$updateRules) : $request->validate(CommissionCategory::$createRules);
        $data = $request->only([
            'name', 'type', 'is_active'
        ]);
        if($id && $service->updateCommissionCategory(CommissionCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createCommissionCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/data/commission-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the commission category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCommissionCategory($id)
    {
        $category = CommissionCategory::find($id);
        return view('admin.commissions._delete_commission_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an commission category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\CommissionService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCommissionCategory(Request $request, CommissionService $service, $id)
    {
        if($id && $service->deleteCommissionCategory(CommissionCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/commission-categories');
    }

    /**
     * Sorts commission categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\CommissionService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCommissionCategory(Request $request, CommissionService $service)
    {
        if($service->sortCommissionCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /******************************************************************************
        COMMISSION TYPES
    *******************************************************************************/

    /**
     * Shows the commission type index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCommissionTypeIndex(Request $request)
    {
        $query = CommissionType::query();
        $data = $request->only(['commission_category_id', 'name']);
        if(isset($data['category_id']) && $data['category_id'] != 'none')
            $query->where('category_id', $data['category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.commissions.commission_types', [
            'types' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + CommissionCategory::orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray()
        ]);
    }

    /**
     * Shows the create commission type page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCommissionType()
    {
        return view('admin.commissions.create_edit_commission_type', [
            'type' => new CommissionType,
            'categories' => CommissionCategory::orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray(),
            'tags' => Tag::orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the edit commission type page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCommissionType($id)
    {
        $commissionType = CommissionType::find($id);
        if(!$commissionType) abort(404);
        return view('admin.commissions.create_edit_commission_type', [
            'type' => $commissionType,
            'categories' => CommissionCategory::orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray(),
            'tags' => Tag::orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Creates or edits a commission type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\CommissionService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCommissionType(Request $request, CommissionService $service, $id = null)
    {
        $request->validate(CommissionType::$rules);
        $data = $request->only([
            'category_id', 'name', 'description', 'is_active', 'is_visible', 'availability',
            'price_type', 'flat_cost', 'cost_min', 'cost_max', 'minimum_cost', 'rate',
            'extras', 'tags', 'show_examples', 'regenerate_key'
        ]);
        if($id && $service->updateCommissionType(CommissionType::find($id), $data, Auth::user())) {
            flash('Commission type updated successfully.')->success();
        }
        else if (!$id && $type = $service->createCommissionType($data, Auth::user())) {
            flash('Commission type created successfully.')->success();
            return redirect()->to('admin/data/commission-types/edit/'.$type->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the commission type deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCommissionType($id)
    {
        $commissionType = CommissionType::find($id);
        return view('admin.commissions._delete_commission_type', [
            'type' => $commissionType,
        ]);
    }

    /**
     * Deletes a commission type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\CommissionService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCommissionType(Request $request, CommissionService $service, $id)
    {
        if($id && $service->deleteCommissionType(CommissionType::find($id))) {
            flash('Commission type deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/commission-types');
    }

    /**
     * Sorts commission types.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\CommissionService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCommissionType(Request $request, CommissionService $service)
    {
        if($service->sortCommissionType($request->get('sort'))) {
            flash('Type order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
