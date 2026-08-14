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
        if ($query === '') {
            return response()->json(['doctors' => [], 'services' => []]);
        }

        $doctors = User::query()
            ->where('role', 4)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('specialization', 'like', "%$query%");
            })
            ->select(['id', 'name', 'specialization'])
            ->limit(10)
            ->get();

        $services = Service::query()
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('description', 'like', "%$query%");
            })
            ->with(['subCatalogs.catalog'])
            ->select(['id', 'name', 'price'])
            ->limit(10)
            ->get()
            ->map(function ($service) {
                $labels = $service->subCatalogs->map(function ($sub) {
                    $catalog = optional($sub->catalog)->name;
                    return trim(($catalog ? $catalog . ' → ' : '') . $sub->name);
                })->filter()->values();

                $primary = $service->subCatalog;

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->formatted_price,
                    'subcatalog_id' => $primary?->id,
                    'catalog' => optional(optional($primary)->catalog)->name,
                    'subcatalog' => optional($primary)->name,
                    'subcatalogs' => $labels->all(),
                    'url' => route('service.booking', $service),
                ];
            });

        return response()->json([
            'doctors' => $doctors,
            'services' => $services,
        ]);
    }
}
