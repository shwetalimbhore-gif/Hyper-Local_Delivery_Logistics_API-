<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hub;

class HubController extends Controller
{
    public function index()
    {
        $hubs = Hub::latest()->get();
        return view('admin.hubs.index', compact('hubs'));
    }

    public function create()
    {
        return view('admin.hubs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        Hub::create($request->all());

        return redirect()->route('hubs.index')->with('success', 'Hub created successfully');
    }

    public function edit(Hub $hub)
    {
        return view('admin.hubs.edit', compact('hub'));
    }

    public function update(Request $request, Hub $hub)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        $hub->update($request->all());

        return redirect()->route('hubs.index')->with('success', 'Hub updated successfully');
    }

    public function destroy(Hub $hub)
    {
        $hub->delete();
        return redirect()->route('hubs.index')->with('success', 'Hub deleted successfully');
    }
}
