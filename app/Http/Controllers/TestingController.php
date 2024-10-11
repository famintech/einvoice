<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Hello World']);
    }
}
