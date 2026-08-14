<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Service;
use App\Models\SubCatalog;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function main()
    {
        // dump('main');
        $catalogs = Catalog::all();
        return view('client.main', compact('catalogs'));
    }

    public function catalog()
    {
        $catalogs = Catalog::with(['subCatalogs' => function ($q) {
            $q->withCount(['services' => function ($sq) {
                $sq->where('services.is_active', true);
            }]);
        }])->get();
        return view('client.catalog', compact('catalogs'));
    }
    public function subCatalog($id)
    {
        $subCatalogs = SubCatalog::where('catalog_id', $id)->withCount(['services' => function ($q) {
            $q->where('services.is_active', true);
        }])->get();
        return view('client.subcatalog', compact('subCatalogs'));
    }
    public function services(int $subCatalogId)
    {
        $subCatalog = SubCatalog::with(['services' => function ($q) {
            $q->where('services.is_active', true)
                ->with(['schedules.user']);
        }, 'catalog'])->findOrFail($subCatalogId);

        $services = $subCatalog->services;

        return view('client.services', compact('subCatalog', 'services'));
    }

    public function booking(Service $service)
    {
        $service->load(['subCatalogs.catalog']);
        // Все активные графики, где доступна данная услуга
        $schedules = $service->schedules()->with('user')->get();
        return view('client.booking', compact('service', 'schedules'));
    }

    public function about()
    {
        return view('client.about');
    }

    public function contacts()
    {
        return view('client.contacts');
    }
}
