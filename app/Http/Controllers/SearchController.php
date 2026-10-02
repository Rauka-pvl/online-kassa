<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['doctors' => [], 'services' => []]);
        }

        $doctors = User::query()
            ->where('role', 4)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('specialization', 'like', '%' . $query . '%');
            })
            ->whereHas('schedules', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('services', fn ($sq) => $sq->where('services.is_active', true));
            })
            ->select(['id', 'name', 'specialization'])
            ->limit(10)
            ->get()
            ->map(function (User $doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                    'url' => route('doctor.show', $doctor),
                ];
            });

        $services = Service::query()
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->whereHas('schedules', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('user', fn ($uq) => $uq->where('role', 4)->where('is_active', true));
            })
            ->with(['subCatalogs.catalog'])
            ->select(['id', 'name', 'price'])
            ->limit(20)
            ->get()
            ->flatMap(function (Service $service) {
                $subs = $service->subCatalogs;

                if ($subs->isEmpty()) {
                    return [[
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->formatted_price,
                        'subcatalog_id' => null,
                        'catalog' => null,
                        'subcatalog' => null,
                        'subcatalogs' => [],
                        'url' => route('service.booking', $service),
                    ]];
                }

                if ($subs->count() === 1) {
                    $sub = $subs->first();
                    $catalogName = optional($sub->catalog)->name;

                    return [[
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->formatted_price,
                        'subcatalog_id' => $sub->id,
                        'catalog' => $catalogName,
                        'subcatalog' => $sub->name,
                        'subcatalogs' => [trim(($catalogName ? $catalogName . ' → ' : '') . $sub->name)],
                        'url' => route('service.booking', [
                            'service' => $service,
                            'sub_catalog' => $sub->id,
                        ]),
                    ]];
                }

                return $subs->map(function ($sub) use ($service) {
                    $catalogName = optional($sub->catalog)->name;
                    $direction = $catalogName ?: $sub->name;

                    return [
                        'id' => $service->id,
                        'name' => $service->name . ' · ' . $direction,
                        'price' => $service->formatted_price,
                        'subcatalog_id' => $sub->id,
                        'catalog' => $catalogName,
                        'subcatalog' => $sub->name,
                        'subcatalogs' => [trim(($catalogName ? $catalogName . ' → ' : '') . $sub->name)],
                        'url' => route('service.booking', [
                            'service' => $service,
                            'sub_catalog' => $sub->id,
                        ]),
                    ];
                });
            })
            ->take(15)
            ->values();

        return response()->json([
            'doctors' => $doctors,
            'services' => $services,
        ]);
    }
}
