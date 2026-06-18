<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailList;
use Illuminate\Http\Request;

class MailListController extends Controller
{
    public function index(Request $request)
    {
        $query = MailList::query();
        if ($search = $request->search) {
            $query->where('name', 'like', "%{$search}%");
        }
        return response()->json([
            'lists' => $query->orderBy('id', 'desc')->paginate($request->per_page ?? 25)
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'custom_fields' => 'nullable|array',
        ]);
        $data['custom_fields'] = json_encode($data['custom_fields'] ?? []);
        $data['creator_name'] = 'Admin';
        $data['date_created'] = now();
        $list = MailList::create($data);
        return response()->json(['list' => $list], 201);
    }

    public function show($id)
    {
        return response()->json(['list' => MailList::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $list = MailList::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'custom_fields' => 'nullable|array',
        ]);
        $data['custom_fields'] = json_encode($data['custom_fields'] ?? []);
        $list->update($data);
        return response()->json(['list' => $list]);
    }

    public function destroy($id)
    {
        MailList::findOrFail($id)->delete();
        return response()->json(['message' => 'Mail list deleted']);
    }
}
