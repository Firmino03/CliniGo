<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Http\Resources\PacienteResource;
use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PacienteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $pacientes = Paciente::query()
            ->when($request->query('busca'), fn ($q, $busca) => $q->where('nome', 'like', "%{$busca}%"))
            ->orderBy('nome')
            ->paginate(15);

        return PacienteResource::collection($pacientes);
    }

    public function store(StorePacienteRequest $request): JsonResponse
    {
        $paciente = Paciente::create($request->validated());

        return PacienteResource::make($paciente)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Paciente $paciente): PacienteResource
    {
        return new PacienteResource($paciente);
    }

    public function update(UpdatePacienteRequest $request, Paciente $paciente): PacienteResource
    {
        $paciente->update($request->validated());

        return new PacienteResource($paciente);
    }

    public function destroy(Paciente $paciente): JsonResponse|Response
    {
        if ($paciente->consultas()->exists() || $paciente->prontuarios()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir um paciente com consultas ou prontuários.',
            ], 409);
        }

        $paciente->delete();

        return response()->noContent();
    }
}