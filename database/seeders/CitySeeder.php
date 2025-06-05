<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run()
    // {
    //     $response = Http::withHeaders([
    //         'key' => env('RAJAONGKIR_API_KEY')
    //     ])->get('https://api.rajaongkir.com/starter/city');
    //     dd($response->json());

    //     if ($response->successful()) {
    //         $cities = $response->json()['rajaongkir']['results'];
    //         foreach ($cities as $city) {
    //             City::create([
    //                 'city_id' => $city['city_id'],
    //                 'province_id' => $city['province_id'],
    //                 'name' => $city['city_name'],
    //                 'type' => $city['type'],
    //                 'postal_code' => $city['postal_code'],
    //             ]);
    //         }
    //     }
    // }
    public function run()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.rajaongkir.com/starter/city');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . env('RAJAONGKIR_API_KEY')
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $cities = $data['rajaongkir']['results'];

        foreach ($cities as $city) {
            City::create([
                'city_id' => $city['city_id'],
                'province_id' => $city['province_id'],
                'name' => $city['city_name'],
                'type' => $city['type'],
                'postal_code' => $city['postal_code'],
            ]);
        }
    }
}
