<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchesController extends Controller
{
    /**
     * Mostrar listado de búsquedas
     */
    public function index(Request $request)
    {
        $authUser = Auth()->user();

        $searches = Search::where('user_id',$authUser->id)->orderBy('created_at', 'desc')->paginate(15);

        // Agregar datos de gráfico para cada búsqueda
        foreach ($searches as $search) {
            $search->chart_data = $this->getChartData($search->id);
        }

        return view('admin_panel.pages.searches.index', compact('searches'));
    }

    /**
     * Obtener datos de gráfico diario de tweets para una búsqueda
     */
    private function getChartData($searchId)
    {
        $days = 7; // Últimos 7 días
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = \App\Models\Tweet::whereJsonContains('matched_search_ids', $searchId)
                ->whereDate('created_at_twitter', $date)
                ->count();

            $data[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d/m'),
                'count' => $count
            ];
        }

        return $data;
    }

    /**
     * Mostrar detalles de una búsqueda con sus tweets
     */
    public function show($id)
    {
        $search = Search::findOrFail($id);

        // Obtener tweets asociados a esta búsqueda
        $tweets = \App\Models\Tweet::whereJsonContains('matched_search_ids', $search->id)
            ->with('account')
            ->orderBy('created_at_twitter', 'desc')
            ->paginate(50);

        // Obtener datos del gráfico
        $search->chart_data = $this->getChartData($search->id);

        return view('admin_panel.pages.searches.show', compact('search', 'tweets'));
    }

    /**
     * Mostrar formulario para crear nueva búsqueda
     */
    public function create()
    {
        $google_api_key = config('constants.google_maps_api_key');
        $search = null;
        return view('admin_panel.pages.searches.create', compact('google_api_key', 'search'));
    }

    /**
     * Guardar nueva búsqueda
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:191',
            'query' => 'required|string',
            'lang' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:5',
            'active' => 'boolean',
            'run_every_minutes' => 'required|integer|min:1',
            'min_like_count' => 'nullable|integer|min:0',
            'min_retweet_count' => 'nullable|integer|min:0',
            'only_from_accounts' => 'nullable|string',
            'query_type' => 'required|in:Latest,Top',
            'timezone' => 'nullable|string|max:40',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validar límites del plan
        $user = auth()->user();
        $company = $user->company;

        if ($company) {
            // Validar límite de búsquedas
            if (!$company->canCreateSearch()) {
                return redirect()->back()
                    ->withErrors([
                        'limit' => "Has alcanzado el límite de {$company->max_searches} búsquedas de tu plan {$company->plan}. Actualiza tu plan para crear más búsquedas."
                    ])
                    ->withInput();
            }

            // Validar frecuencia mínima
            $requestedFrequency = $request->run_every_minutes;
            if ($requestedFrequency < $company->max_frequency_minutes) {
                return redirect()->back()
                    ->withErrors([
                        'run_every_minutes' => "La frecuencia mínima para tu plan {$company->plan} es de {$company->max_frequency_minutes} minutos. Actualmente intentas configurar {$requestedFrequency} minutos."
                    ])
                    ->withInput();
            }
        }

        $data = $request->except('only_from_accounts');

        // Convertir only_from_accounts de string a array
        if ($request->filled('only_from_accounts')) {
            $accounts = array_filter(
                array_map('trim', explode(',', $request->only_from_accounts))
            );
            $data['only_from_accounts'] = $accounts;
        } else {
            $data['only_from_accounts'] = null;
        }

        // Asegurarse que active sea boolean
        $data['active'] = $request->has('active') ? true : false;
        $data['user_id'] = auth()->id();

        Search::create($data);

        return redirect()->route('admin::searches.index')
            ->with('success', 'Búsqueda creada correctamente');
    }

    /**
     * Mostrar formulario para editar búsqueda
     */
    public function edit($id)
    {
        $search = Search::findOrFail($id);
        $google_api_key = config('constants.google_maps_api_key');
        return view('admin_panel.pages.searches.create', compact('search', 'google_api_key'));
    }

    /**
     * Actualizar búsqueda existente
     */
    public function update(Request $request, $id)
    {
        $search = Search::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:191',
            'query' => 'required|string',
            'lang' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:5',
            'active' => 'boolean',
            'run_every_minutes' => 'required|integer|min:1',
            'min_like_count' => 'nullable|integer|min:0',
            'min_retweet_count' => 'nullable|integer|min:0',
            'only_from_accounts' => 'nullable|string',
            'query_type' => 'required|in:Latest,Top',
            'timezone' => 'nullable|string|max:40',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('only_from_accounts');

        // Convertir only_from_accounts de string a array
        if ($request->filled('only_from_accounts')) {
            $accounts = array_filter(
                array_map('trim', explode(',', $request->only_from_accounts))
            );
            $data['only_from_accounts'] = $accounts;
        } else {
            $data['only_from_accounts'] = null;
        }

        // Asegurarse que active sea boolean
        $data['active'] = $request->has('active') ? true : false;

        $search->update($data);

        return redirect()->route('admin::searches.index')
            ->with('success', 'Búsqueda actualizada correctamente');
    }

    /**
     * Eliminar búsqueda (soft delete)
     */
    public function destroy($id)
    {
        $search = Search::findOrFail($id);
        $search->delete();

        return redirect()->route('admin::searches.index')
            ->with('success', 'Búsqueda eliminada correctamente');
    }

    /**
     * Activar/Desactivar búsqueda
     */
    public function toggleActive($id)
    {
        $search = Search::findOrFail($id);
        $search->active = !$search->active;
        $search->save();

        $status = $search->active ? 'activada' : 'desactivada';
        return redirect()->route('admin::searches.index')
            ->with('success', "Búsqueda {$status} correctamente");
    }
}
