<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SepatuController extends Controller
{
    public function index()
    {
        return view('admin.sepatu.index');
    }
}
