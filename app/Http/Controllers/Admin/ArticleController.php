<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        return view('admin.articles.index');
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(Request $request)
    {
        // Logic untuk menyimpan article baru
    }

    public function edit($id)
    {
        return view('admin.articles.edit');
    }

    public function update(Request $request, $id)
    {
        // Logic untuk update article
    }

    public function destroy($id)
    {
        // Logic untuk delete article
    }
}
