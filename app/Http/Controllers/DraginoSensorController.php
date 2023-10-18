<?php

namespace App\Http\Controllers;

use App\Models\SensorAirTemperature;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client;
use DateTime;

class DraginoSensorController extends Controller
{
    //
    public function AirTemperaturePull()
    {
        // Pull data from API Thingspeak Air Temperature
        $client = new Client();
        $response = $client->get('https://api.thingspeak.com/channels/1285589/fields/6/last.json?timezone=Asia/Jakarta');
        $data = json_decode($response->getBody(), true);

        //Trasnform data Json into variable
        $airtemp = $data['field6'];
        $timestamp = $data['created_at'];

        $dateTime = new DateTime($timestamp);
        $date = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i');

        // Data Validation check in database to prevent the duplication
        if (!SensorAirTemperature::where('time', $time)->count() > 0) {
                        $dataAirtTemp = new SensorAirTemperature(
                [
                    'temperature' => $airtemp,
                    'date' => $date,
                    'time' => $time
                ]
            );

            $dataAirtTemp->save();
            $statusReturn = ['status' => 'true', 'message' => 'Data has been fetched and stored in the database'];
        } else {
            $statusReturn = ['status' => 'false', 'message' => 'Data has been fetched but data already exists in the database'];
        }

        return response()->json($statusReturn);
    }

    public function AirTemperatureData(){
        $data = SensorAirTemperature::all();

        $filteredData = $data->map(function ($item) {
            return [
                'value' => $item->temperature,
                'date' => $item->date,
            ];
        });

        //dd($filteredData);

        return response()->json(['data'=>$filteredData,'status'=> 'true']);
    }
}