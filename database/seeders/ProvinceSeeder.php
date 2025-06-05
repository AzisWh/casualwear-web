<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.rajaongkir.com/starter/province');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . env('RAJAONGKIR_API_KEY')
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $provinces = $data['rajaongkir']['results'];

        foreach ($provinces as $province) {
            Province::create([
                'province_id' => $province['province_id'],
                'name' => $province['province'],
            ]);
        }
    }

}
