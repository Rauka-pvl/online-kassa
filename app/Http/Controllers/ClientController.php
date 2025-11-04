<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\SubCatalog;
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
        $catalogs = Catalog::all();
        return view('client.catalog', compact('catalogs'));
    }
    public function subCatalog($id)
    {
        $subCatalogs = SubCatalog::where('catalog_id', $id)->get();
        return view('client.subcatalog', compact('subCatalogs'));
    }
    public function services()
    {
        $services = [
            (object)[
                'name' => 'Услуга 1',
                'description' => 'Описание услуги 1'
            ],
            (object)[
                'name' => 'Услуга 2',
                'description' => 'Описание услуги 2'
            ],
            (object)[
                'name' => 'Услуга 3',
                'description' => 'Описание услуги 3'
            ]
        ];
        return view('client.services', compact('services'));
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
