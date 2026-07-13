<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index()
    {
        return view('admin.ports.index');
    }

    public function create()
    {
        return view('admin.ports.create');
    }

    public function store(Request $request)
    {
        // Logic untuk menyimpan port baru
    }

    public function edit($id)
    {
        return view('admin.ports.edit');
    }

    public function update(Request $request, $id)
    {
        // Logic untuk update port
    }

    public function destroy($id)
    {
        // Logic untuk delete port
    }
}
