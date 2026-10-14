<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Site\DataUsulanService;
use App\Services\Site\SiteDataService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(SiteDataService $siteData, DataUsulanService $dataUsulan): View
    {
        return view('site.home', [
            'recentReports' => $siteData->latestPublicReports(3),
            'recentUsulan' => $dataUsulan->usulanTerbaruBeranda(3),
        ]);
    }
}
