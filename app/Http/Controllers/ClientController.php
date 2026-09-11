<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Service;
use App\Models\SubCatalog;
use App\Models\User;

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
        $schedules = $service->schedules()
            ->where('is_active', true)
            ->with('user')
            ->get();

        return view('client.booking', compact('service', 'schedules'));
    }

    public function doctor(User $user)
    {
        abort_unless($user->role == 4 && $user->is_active, 404);

        $schedules = $user->schedules()
            ->where('is_active', true)
            ->with(['services' => function ($q) {
                $q->where('services.is_active', true)->with(['subCatalogs.catalog']);
            }])
            ->get();

        $services = $schedules
            ->flatMap(function ($schedule) {
                return $schedule->services->map(function ($service) use ($schedule) {
                    $clone = clone $service;
                    $clone->booking_schedule_id = $schedule->id;

                    return $clone;
                });
            })
            ->unique('id')
            ->values();

        return view('client.doctor', [
            'doctor' => $user,
            'services' => $services,
            'schedules' => $schedules,
        ]);
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
