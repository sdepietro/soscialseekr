<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Search;
use App\Models\UserOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario del paso 2: Información de empresa
     */
    public function step2()
    {
        $user = auth()->user();
        $onboarding = $user->onboarding;

        // Si no existe onboarding o ya completó el paso 2, redirigir
        if (!$onboarding) {
            return redirect()->route('admin::home');
        }

        if ($onboarding->step2_company) {
            return redirect()->route('onboarding.step3');
        }

        $industries = config('plans.industries');
        $companySizes = [
            '1-10' => '1-10 empleados',
            '11-50' => '11-50 empleados',
            '51-200' => '51-200 empleados',
            '201-500' => '201-500 empleados',
            '500+' => 'Más de 500 empleados',
        ];

        return view('admin_panel.pages.onboarding.step2', compact('industries', 'companySizes'));
    }

    /**
     * Guardar información de empresa (Paso 2)
     */
    public function storeStep2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:191',
            'industry' => 'required|string|max:100',
            'company_size' => 'required|in:1-10,11-50,51-200,201-500,500+',
            'country' => 'required|string|max:5',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $attributeNames = [
            'company_name' => 'nombre de la empresa',
            'industry' => 'sector/industria',
            'company_size' => 'tamaño de la empresa',
            'country' => 'país',
            'website' => 'sitio web',
            'phone' => 'teléfono',
        ];

        $validator->setAttributeNames($attributeNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        // Crear empresa con plan free y trial de 14 días
        $company = Company::create([
            'name' => $request->company_name,
            'industry' => $request->industry,
            'company_size' => $request->company_size,
            'country' => $request->country,
            'website' => $request->website,
            'phone' => $request->phone,
            'plan' => 'free',
            'max_searches' => 3,
            'max_frequency_minutes' => 60,
            'can_use_ai' => false,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Asociar usuario con empresa
        $user->update([
            'company_id' => $company->id,
            'is_company_admin' => true,
        ]);

        // Actualizar onboarding
        $user->onboarding->update([
            'current_step' => 3,
            'step2_company' => true,
        ]);

        return redirect()->route('onboarding.step3');
    }

    /**
     * Mostrar formulario del paso 3: Primera búsqueda
     */
    public function step3()
    {
        $user = auth()->user();
        $onboarding = $user->onboarding;

        // Verificar que haya completado paso 2
        if (!$onboarding || !$onboarding->step2_company) {
            return redirect()->route('onboarding.step2');
        }

        // Si ya completó el paso 3, redirigir al dashboard
        if ($onboarding->step3_search) {
            return redirect()->route('admin::home');
        }

        $company = $user->company;
        $industry = $company->industry ?? 'default';

        // Obtener templates según industria
        $templates = config("plans.search_templates.{$industry}", config('plans.search_templates.default'));

        return view('admin_panel.pages.onboarding.step3', compact('templates', 'company'));
    }

    /**
     * Guardar primera búsqueda (Paso 3)
     */
    public function storeStep3(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        // Validar según si usa template o personalizado
        if ($request->use_template && $request->template_index !== null) {
            // Usar template predefinido
            $industry = $company->industry ?? 'default';
            $templates = config("plans.search_templates.{$industry}", config('plans.search_templates.default'));
            $templateIndex = (int) $request->template_index;

            if (!isset($templates[$templateIndex])) {
                return back()->withErrors(['template' => 'Template no válido'])->withInput();
            }

            $template = $templates[$templateIndex];

            $searchData = [
                'user_id' => $user->id,
                'name' => $template['name'],
                'query' => $template['query'],
                'country' => $template['country'],
                'lang' => $template['lang'],
                'ia_prompt' => $template['ia_prompt'],
                'active' => false,
                'query_type' => 'Latest',
                'run_every_minutes' => $company->max_frequency_minutes,
            ];
        } else {
            // Búsqueda personalizada
            $validator = Validator::make($request->all(), [
                'search_name' => 'required|string|max:191',
                'keywords' => 'required|string|max:500',
                'country' => 'required|string|max:5',
                'lang' => 'nullable|string|max:10',
            ]);

            $attributeNames = [
                'search_name' => 'nombre de la búsqueda',
                'keywords' => 'palabras clave',
                'country' => 'país',
                'lang' => 'idioma',
            ];

            $validator->setAttributeNames($attributeNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Construir query simple
            $keywords = $request->keywords;
            $query = "min_replies:1 ({$keywords}) -filter:replies";

            $searchData = [

                'user_id' => $user->id,
                'name' => $request->search_name,
                'query' => $query,
                'country' => $request->country,
                'lang' => $request->lang ?? 'es',
                'active' => false,
                'query_type' => 'Latest',
                'run_every_minutes' => $company->max_frequency_minutes,
            ];
        }

        // Crear la búsqueda
        Search::create($searchData);

        // Completar onboarding
        $user->onboarding->update([
            'current_step' => 4,
            'step3_search' => true,
            'completed_at' => now(),
        ]);

        return redirect()->route('admin::home')->with('success', '¡Bienvenido a X Finder! Tu cuenta está lista.');
    }

    /**
     * Saltar un paso del onboarding
     */
    public function skip(int $step)
    {
        $user = auth()->user();
        $onboarding = $user->onboarding;

        if (!$onboarding) {
            return redirect()->route('admin::home');
        }

        // Solo permitir saltar el paso 3 (búsqueda inicial)
        if ($step === 3 && !$onboarding->step3_search) {
            // Crear búsqueda de ejemplo inactiva
            Search::create([
                'user_id' => $user->id,
                'name' => 'Búsqueda de Ejemplo (Inactiva)',
                'query' => 'min_replies:1 (ejemplo OR tutorial) -filter:replies',
                'country' => $user->company->country ?? 'AR',
                'lang' => 'es',
                'active' => false,
                'query_type' => 'Latest',
                'run_every_minutes' => $user->company->max_frequency_minutes ?? 60,
            ]);

            // Marcar como saltado
            $onboarding->skipStep($step);
            $onboarding->update([
                'step3_search' => true,
                'completed_at' => now(),
            ]);

            return redirect()->route('admin::home')->with('success', 'Puedes configurar tus búsquedas más tarde desde el menú.');
        }

        return redirect()->route('admin::home');
    }
}
