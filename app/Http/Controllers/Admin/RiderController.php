<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rider;

class RiderController extends Controller
{
    public function index()
    {
        $riders = Rider::latest()->get();
        return view('admin.riders.index', compact('riders'));
    }

    public function create()
    {
        return view('admin.riders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required'
        ]);

        Rider::create($request->all());

        return redirect()->route('riders.index')->with('success', 'Rider created successfully');
    }

    public function edit(Rider $rider)
    {
        return view('admin.riders.edit', compact('rider'));
    }

    public function update(Request $request, Rider $rider)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required'
        ]);

        $rider->update($request->all());

        return redirect()->route('riders.index')->with('success', 'Rider updated successfully');
    }

}
