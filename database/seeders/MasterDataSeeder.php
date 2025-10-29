<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\StorageLocation;
use App\Models\Supplier;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // Categories
        $categories = [
            ['name'=>'Rem','code'=>'CAT-REM'],
            ['name'=>'Kelistrikan','code'=>'CAT-KEL'],
            ['name'=>'Body','code'=>'CAT-BODY'],
            ['name'=>'Suspensi','code'=>'CAT-SUS'],
            ['name'=>'Oli & Pelumas','code'=>'CAT-PEL'],
        ];
        foreach ($categories as $c) {
            Category::updateOrCreate(['code'=>$c['code']], $c);
        }

        // Brands
        $brands = [
            ['name'=>'MerekA','code'=>'BR-MA'],
            ['name'=>'MerekB','code'=>'BR-MB'],
        ];
        foreach ($brands as $b) {
            Brand::updateOrCreate(['code'=>$b['code']], $b);
        }

        // Storage locations
        $locations = [
            ['name'=>'Gudang Utama','code'=>'LOC-GUD-UTAMA'],
            ['name'=>'Etalase Depan','code'=>'LOC-ETALASE'],
        ];
        foreach ($locations as $l) {
            StorageLocation::updateOrCreate(['code'=>$l['code']], $l);
        }

        // Suppliers
        $suppliers = [
            ['name'=>'PT. Supplier Satu','code'=>'SUP-001','phone'=>'08123456789','email'=>'sup1@vendor.test','address'=>'Jl. Supplier No.1'],
            ['name'=>'CV. Supplier Dua','code'=>'SUP-002','phone'=>'08129876543','email'=>'sup2@vendor.test','address'=>'Jl. Supplier No.2'],
        ];
        foreach ($suppliers as $s) {
            Supplier::updateOrCreate(['code'=>$s['code']], $s);
        }
    }
}
