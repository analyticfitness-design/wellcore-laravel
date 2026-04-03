<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFitcron;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EjerciciosController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EjercicioFitcron::query();

        if ($grupo = $request->query('grupo_muscular')) {
            $query->porGrupo($grupo);
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($dificultad = $request->query('dificultad')) {
            $query->where('dificultad', (int) $dificultad);
        }

        if ($search = $request->query('search')) {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $ejercicios = $query->orderBy('nombre')->paginate(20);

        $ejercicios->getCollection()->transform(function (EjercicioFitcron $e) {
            return $this->formatEjercicio($e);
        });

        return response()->json($ejercicios);
    }

    public function show(string $slug): JsonResponse
    {
        $ejercicio = EjercicioFitcron::where('slug', $slug)->firstOrFail();

        return response()->json($this->formatEjercicio($ejercicio));
    }

    private function formatEjercicio(EjercicioFitcron $ejercicio): array
    {
        return [
            'id'                    => $ejercicio->id,
            'slug'                  => $ejercicio->slug,
            'nombre'                => $ejercicio->nombre,
            'tipo'                  => $ejercicio->tipo,
            'grupo_muscular'        => $ejercicio->grupo_muscular,
            'musculos_involucrados' => $ejercicio->musculos_involucrados,
            'equipamiento'          => $ejercicio->equipamiento,
            'dificultad'            => $ejercicio->dificultad,
            'gif_url'               => $ejercicio->gif_url,
            'gif_filename'          => $ejercicio->gif_filename,
            'gif_path'              => $ejercicio->gif_path,
            'gif_local_url'         => $ejercicio->gif_local_url,
            'descargado'            => $ejercicio->descargado,
            'created_at'            => $ejercicio->created_at,
            'updated_at'            => $ejercicio->updated_at,
        ];
    }
}
