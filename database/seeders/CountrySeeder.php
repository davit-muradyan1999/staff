<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['id' => 1, 'name' => ['ru' => 'Россия'], 'created_at' => date('Y-m-d H:i:s')],
        ];

        foreach($countries as $country) {
            Country::firstOrCreate([
                'id' => $country['id']
            ], $country);
        }
    }
}
