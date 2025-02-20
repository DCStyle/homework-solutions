<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::topLevel()->with('allChildren')->get();
        return view('admin.menu.index', compact('menuItems'));
    }

    public function create()
    {
        $parentItems = MenuItem::topLevel()->with('allChildren')->get();
        $categories = Category::orderBy('id')->get();
        return view('admin.menu.form', compact('parentItems', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'type' => ['required', Rule::in(['link', 'dropdown', 'category'])],
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
            'category_id' => 'required_if:type,category|nullable|exists:categories,id'
        ]);

        try {
            DB::beginTransaction();

            // If no order specified, put at the end
            if (!isset($validated['order'])) {
                $maxOrder = MenuItem::where('parent_id', $validated['parent_id'])->max('order');
                $validated['order'] = $maxOrder ? $maxOrder + 1 : 0;
            }

            MenuItem::create($validated);

            DB::commit();
            return redirect()->route('admin.menu.index')
                ->with('success', 'Menu item created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating menu item: ' . $e->getMessage());
        }
    }

    public function edit(MenuItem $menuItem)
    {
        $parentItems = MenuItem::topLevel()
            ->with('allChildren')
            ->where('id', '!=', $menuItem->id)
            ->get();
        $categories = Category::orderBy('name')->get(); // Add this line
        return view('admin.menu.form', compact('menuItem', 'parentItems', 'categories'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'type' => ['required', Rule::in(['link', 'dropdown', 'category'])],
            'parent_id' => [
                'nullable',
                'exists:menu_items,id',
                function ($attribute, $value, $fail) use ($menuItem) {
                    if ($value == $menuItem->id) {
                        $fail('A menu item cannot be its own parent.');
                    }
                }
            ],
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
            'category_id' => 'required_if:type,category|nullable|exists:categories,id'
        ]);

        try {
            DB::beginTransaction();

            $menuItem->update($validated);

            DB::commit();
            return redirect()->route('admin.menu.index')
                ->with('success', 'Menu item updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating menu item: ' . $e->getMessage());
        }
    }

    public function destroy(MenuItem $menuItem)
    {
        try {
            DB::beginTransaction();

            $menuItem->delete();

            DB::commit();
            return redirect()->route('admin.menu.index')
                ->with('success', 'Menu item deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting menu item: ' . $e->getMessage());
        }
    }

    public function reorder(Request $request)
    {
        try {
            DB::beginTransaction();

            $itemId = $request->input('itemId');
            $newIndex = $request->input('newIndex');
            $parentId = $request->input('parentId');
            $children = $request->input('children', []);

            $item = MenuItem::findOrFail($itemId);

            // Update the parent_id of the dragged item
            $item->parent_id = $parentId;
            $item->save();

            // Get all siblings (items with the same parent)
            $siblings = MenuItem::where('parent_id', $parentId)
                ->orderBy('order')
                ->get();

            // Remove the item from its current position
            $siblings = $siblings->filter(function ($sibling) use ($itemId) {
                return $sibling->id != $itemId;
            });

            // Insert the item at the new position
            $siblings->splice($newIndex, 0, [$item]);

            // Update order for all siblings
            foreach ($siblings as $index => $sibling) {
                $sibling->update(['order' => $index + 1]);
            }

            // Update children parent_ids if needed
            foreach ($children as $child) {
                MenuItem::where('id', $child['id'])
                    ->update(['parent_id' => $child['parentId']]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
