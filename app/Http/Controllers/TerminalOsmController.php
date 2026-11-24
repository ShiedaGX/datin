<?php

namespace App\Http\Controllers;

use App\Models\FasilitasTransportasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalOsmController extends Controller
{
    public function geojson(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        $query = FasilitasTransportasi::query()
            ->where('id_moda', 1)
            ->where(
                'sumber_data',
                'OpenStreetMap via Overpass Turbo'
            )
            ->where('aktif', 1);

        if ($request->filled('status')) {
            $query->where(
                'status_verifikasi',
                $request->query('status')
            );
        }

        $features = $query->get()->map(function ($terminal) {
            return [
                'type' => 'Feature',

                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $terminal->longitude,
                        (float) $terminal->latitude,
                    ],
                ],

                'properties' => [
                    'id' => $terminal->id_fasilitas,
                    'nama' => $terminal->nama,
                    'tipe_terminal' => $terminal->tipe_terminal,
                    'status_verifikasi' => $terminal->status_verifikasi,
                    'wilayah' => $terminal->wilayah_osm,
                    'sumber' => $terminal->sumber_data,
                ],
            ];
        })->values()->all();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
