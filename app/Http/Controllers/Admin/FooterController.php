<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterColumn;
use App\Models\FooterLink;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function index()
    {
        $columns = FooterColumn::with('links')
            ->orderBy('position')
            ->get();

        return view('admin.footer.index', compact('columns'));
    }

    public function storeColumn(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $position = FooterColumn::max('position') + 1;

        FooterColumn::create([
            'title' => $request->title,
            'position' => $position,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.footer.index')
            ->with('success', 'Column added successfully');
    }

    public function updateColumn(Request $request, FooterColumn $column)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $column->update([
            'title' => $request->title,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.footer.index')
            ->with('success', 'Column updated successfully');
    }

    public function destroyColumn(FooterColumn $column)
    {
        $column->delete();

        return redirect()->route('admin.footer.index')
            ->with('success', 'Column deleted successfully');
    }

    public function storeLink(Request $request, FooterColumn $column)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string',
        ]);

        $position = $column->links()->max('position') + 1;

        $column->links()->create([
            'title' => $request->title,
            'url' => $request->url,
            'position' => $position,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.footer.index')
            ->with('success', 'Link added successfully');
    }

    public function updateLink(Request $request, FooterLink $link)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $link->update([
            'title' => $request->title,
            'url' => $request->url,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.footer.index')
            ->with('success', 'Link updated successfully');
    }

    public function destroyLink(FooterLink $link)
    {
        $link->delete();

        return redirect()->route('admin.footer.index')
            ->with('success', 'Link deleted successfully');
    }

    public function updatePositions(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:footer_columns,id',
            'columns.*.position' => 'required|integer|min:0',
            'columns.*.links' => 'sometimes|array',
            'columns.*.links.*.id' => 'sometimes|exists:footer_links,id',
            'columns.*.links.*.position' => 'sometimes|integer|min:0',
        ]);

        foreach ($request->columns as $columnData) {
            FooterColumn::where('id', $columnData['id'])->update([
                'position' => $columnData['position']
            ]);

            if (isset($columnData['links']) && is_array($columnData['links'])) {
                foreach ($columnData['links'] as $linkData) {
                    FooterLink::where('id', $linkData['id'])->update([
                        'position' => $linkData['position']
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
