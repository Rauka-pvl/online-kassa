<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Service;
use App\Models\SubCatalog;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function main()
    {
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
            $q->where('services.is_active', true);
        }, 'catalog'])->findOrFail($subCatalogId);

        $services = $subCatalog->services->map(function (Service $service) use ($subCatalogId) {
            $service->setRelation(
                'schedules',
                $service->activeSchedulesForSubCatalog($subCatalogId)
            );

            return $service;
        });

        return view('client.services', compact('subCatalog', 'services'));
    }

    public function booking(Request $request, Service $service)
    {
        abort_unless($service->is_active, 404);

        $service->load(['subCatalogs.catalog']);

        $subCatalogId = $request->integer('sub_catalog') ?: null;
        $contextSubCatalog = $service->resolveSubCatalog($subCatalogId);

        if ($subCatalogId && !$contextSubCatalog) {
            abort(404);
        }

        if (!$contextSubCatalog && $service->subCatalogs->count() > 1) {
            return view('client.booking-choose-direction', compact('service'));
        }

        $schedules = $service->activeSchedulesForSubCatalog($contextSubCatalog?->id);

        return view('client.booking', [
            'service' => $service,
            'schedules' => $schedules,
            'contextSubCatalog' => $contextSubCatalog,
        ]);
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

                    $matchedSub = $service->subCatalogs->first(
                        fn ($sub) => $service->scheduleMatchesSubCatalog($schedule, $sub)
                    ) ?? $service->subCatalogs->first();

                    $clone->booking_sub_catalog_id = $matchedSub?->id;

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
