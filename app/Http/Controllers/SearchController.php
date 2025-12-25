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
            ->with(['subCatalog.catalog'])
            ->select(['id', 'name', 'sub_catalog_id', 'price'])
            ->limit(10)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->formatted_price,
                    'subcatalog_id' => $service->sub_catalog_id,
                    'catalog' => optional(optional($service->subCatalog)->catalog)->name,
                    'subcatalog' => optional($service->subCatalog)->name,
                ];
            });

        return response()->json([
            'doctors' => $doctors,
            'services' => $services,
        ]);
    }
}


