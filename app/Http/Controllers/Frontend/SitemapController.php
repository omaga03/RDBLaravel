<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\RdbResearcher;
use App\Models\RdbProjectUtilize;
use App\Models\RdbDip;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    /**
     * Return Sitemap Index (XML)
     */
    public function index(): Response
    {
        $formatDate = function ($date) {
            if (!$date) return now()->toAtomString();
            try {
                return Carbon::parse($date)->toAtomString();
            } catch (\Throwable $e) {
                return now()->toAtomString();
            }
        };

        $sitemaps = [
            [
                'loc' => url('/sitemap-projects.xml'),
                'lastmod' => $formatDate(RdbProject::where('data_show', 1)->max('updated_at')),
            ],
            [
                'loc' => url('/sitemap-publications.xml'),
                'lastmod' => $formatDate(RdbPublished::max('updated_at')),
            ],
            [
                'loc' => url('/sitemap-researchers.xml'),
                'lastmod' => $formatDate(RdbResearcher::max('updated_at')),
            ],
            [
                'loc' => url('/sitemap-utilizations.xml'),
                'lastmod' => $formatDate(RdbProjectUtilize::max('updated_at')),
            ],
            [
                'loc' => url('/sitemap-ip.xml'),
                'lastmod' => $formatDate(RdbDip::max('updated_at')),
            ],
        ];

        return response()->view('frontend.sitemap.index', compact('sitemaps'))
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex');
    }

    /**
     * Projects Sitemap
     */
    public function projects(): Response
    {
        $projects = RdbProject::where('data_show', 1)
            ->select('pro_id', 'updated_at', 'created_at', 'pro_date_end')
            ->orderBy('pro_id', 'desc')
            ->get();

        $urls = $projects->map(function ($item) {
            $lastmod = $item->updated_at ?? $item->created_at ?? $item->pro_date_end;
            return [
                'loc' => route('frontend.rdbproject.show', $item->pro_id),
                'lastmod' => $this->toAtom($lastmod),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        });

        return response()->view('frontend.sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Publications Sitemap
     */
    public function publications(): Response
    {
        $publications = RdbPublished::select('id', 'updated_at', 'created_at', 'pub_date')
            ->orderBy('id', 'desc')
            ->get();

        $urls = $publications->map(function ($item) {
            $lastmod = $item->updated_at ?? $item->created_at ?? $item->pub_date;
            return [
                'loc' => route('frontend.rdbpublished.show', $item->id),
                'lastmod' => $this->toAtom($lastmod),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        });

        return response()->view('frontend.sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Researchers Sitemap
     */
    public function researchers(): Response
    {
        $researchers = RdbResearcher::select('researcher_id', 'updated_at', 'created_at')
            ->orderBy('researcher_id', 'desc')
            ->get();

        $urls = $researchers->map(function ($item) {
            $lastmod = $item->updated_at ?? $item->created_at;
            return [
                'loc' => route('frontend.rdbresearcher.show', $item->researcher_id),
                'lastmod' => $this->toAtom($lastmod),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        });

        return response()->view('frontend.sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Utilizations Sitemap
     */
    public function utilizations(): Response
    {
        $utilizations = RdbProjectUtilize::select('utz_id', 'updated_at', 'created_at', 'utz_date')
            ->orderBy('utz_id', 'desc')
            ->get();

        $urls = $utilizations->map(function ($item) {
            $lastmod = $item->updated_at ?? $item->created_at ?? $item->utz_date;
            return [
                'loc' => route('frontend.rdbprojectutilize.show', $item->utz_id),
                'lastmod' => $this->toAtom($lastmod),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        });

        return response()->view('frontend.sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Intellectual Property Sitemap
     */
    public function ip(): Response
    {
        $dips = RdbDip::select('dip_id', 'updated_at', 'created_at', 'dip_request_date')
            ->orderBy('dip_id', 'desc')
            ->get();

        $urls = $dips->map(function ($item) {
            $lastmod = $item->updated_at ?? $item->created_at ?? $item->dip_request_date;
            return [
                'loc' => route('frontend.rdbdip.show', $item->dip_id),
                'lastmod' => $this->toAtom($lastmod),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        });

        return response()->view('frontend.sitemap.urlset', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Safe Carbon Atom date formatter
     */
    protected function toAtom($date): string
    {
        if (!$date) return now()->toAtomString();
        try {
            return Carbon::parse($date)->toAtomString();
        } catch (\Throwable $e) {
            return now()->toAtomString();
        }
    }
}
