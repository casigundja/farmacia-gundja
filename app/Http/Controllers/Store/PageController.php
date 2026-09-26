<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('store.pages.about');
    }

    public function services(): View
    {
        return view('store.pages.services');
    }

    public function branches(): View
    {
        $branches = Branch::query()->where('active', true)->get();

        return view('store.pages.branches', compact('branches'));
    }

    public function contact(): View
    {
        $branches = Branch::query()->where('active', true)->get();

        return view('store.pages.contact', compact('branches'));
    }

    public function faq(): View
    {
        return view('store.pages.faq');
    }

    public function privacy(): View
    {
        return view('store.pages.privacy');
    }

    public function terms(): View
    {
        return view('store.pages.terms');
    }
}
