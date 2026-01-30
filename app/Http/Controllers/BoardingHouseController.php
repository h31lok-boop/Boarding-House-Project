<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use Illuminate\Http\Request;

class BoardingHouseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $houses = BoardingHouse::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.boarding-houses.index', compact('houses'));
    }

    public function create()
    {
        return view('admin.boarding-houses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        BoardingHouse::create($data);

        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house created.');
    }

    public function edit(BoardingHouse $boarding_house)
    {
        return view('admin.boarding-houses.edit', ['house' => $boarding_house]);
    }

    public function update(Request $request, BoardingHouse $boarding_house)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $boarding_house->update($data);

        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house updated.');
    }

    public function destroy(BoardingHouse $boarding_house)
    {
        $boarding_house->delete();
        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house deleted.');
    }
}
