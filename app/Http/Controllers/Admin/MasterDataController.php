<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\QrSource;
use App\Models\Subcategory;
use Illuminate\Contracts\View\View;

class MasterDataController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.master-data.index', [
            'dusuns' => Dusun::query()->withCount('reports')->orderBy('name')->get(),
            'categories' => Category::query()->withCount(['subcategories'])->orderBy('sort_order')->orderBy('name')->get(),
            'subcategories' => Subcategory::query()->with(['category:id,name'])->withCount('reports')->orderBy('category_id')->orderBy('sort_order')->get(),
            'qrSources' => QrSource::query()->with('dusun:id,name')->withCount('reports')->orderBy('name')->get(),
        ]);
    }
}
