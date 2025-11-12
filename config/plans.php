<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Planes de Suscripción - X Finder
    |--------------------------------------------------------------------------
    |
    | Definición de los planes disponibles en el sistema SAAS.
    | Cada plan define límites y funcionalidades disponibles.
    |
    */

    'free' => [
        'name' => 'Free Trial',
        'slug' => 'free',
        'description' => 'Perfecto para empezar y probar la plataforma',
        'price' => 0,
        'currency' => 'USD',
        'billing_period' => 'trial',

        // Límites
        'max_searches' => 3,
        'max_frequency_minutes' => 60,
        'can_use_ai' => false,
        'max_tweets_per_search' => 100,
        'historical_data_days' => 7,

        // Trial
        'trial_days' => 14,
        'requires_payment_method' => false,

        // Features
        'features' => [
            'Hasta 3 búsquedas simultáneas',
            'Actualización cada 60 minutos',
            'Historial de 7 días',
            'Exportación básica (CSV)',
            'Soporte por email',
        ],
    ],

    'starter' => [
        'name' => 'Starter',
        'slug' => 'starter',
        'description' => 'Ideal para pequeños negocios y emprendedores',
        'price' => 49,
        'currency' => 'USD',
        'billing_period' => 'monthly',

        // Límites
        'max_searches' => 10,
        'max_frequency_minutes' => 30,
        'can_use_ai' => true,
        'max_tweets_per_search' => 500,
        'historical_data_days' => 30,

        // Trial
        'trial_days' => 0,
        'requires_payment_method' => true,

        // Features
        'features' => [
            'Hasta 10 búsquedas simultáneas',
            'Actualización cada 30 minutos',
            'Análisis de IA incluido',
            'Historial de 30 días',
            'Alertas por email',
            'Exportación avanzada (CSV, JSON)',
            'Soporte prioritario',
        ],
    ],

    'professional' => [
        'name' => 'Professional',
        'slug' => 'professional',
        'description' => 'Para equipos y empresas en crecimiento',
        'price' => 99,
        'currency' => 'USD',
        'billing_period' => 'monthly',

        // Límites
        'max_searches' => 50,
        'max_frequency_minutes' => 15,
        'can_use_ai' => true,
        'max_tweets_per_search' => 2000,
        'historical_data_days' => 90,

        // Trial
        'trial_days' => 0,
        'requires_payment_method' => true,

        // Features
        'features' => [
            'Hasta 50 búsquedas simultáneas',
            'Actualización cada 15 minutos',
            'Análisis de IA avanzado',
            'Historial de 90 días',
            'Alertas personalizadas',
            'Webhooks y API',
            'Exportación completa',
            'Dashboard personalizado',
            'Soporte 24/7',
        ],
    ],

    'enterprise' => [
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'description' => 'Solución completa para grandes organizaciones',
        'price' => 299,
        'currency' => 'USD',
        'billing_period' => 'monthly',

        // Límites
        'max_searches' => -1, // Ilimitado
        'max_frequency_minutes' => 5,
        'can_use_ai' => true,
        'max_tweets_per_search' => -1, // Ilimitado
        'historical_data_days' => 365,

        // Trial
        'trial_days' => 0,
        'requires_payment_method' => true,

        // Features
        'features' => [
            'Búsquedas ilimitadas',
            'Actualización cada 5 minutos',
            'IA con modelos personalizados',
            'Historial de 1 año',
            'Multi-usuario y roles',
            'API ilimitada',
            'White-label disponible',
            'Servidor dedicado opcional',
            'Account manager dedicado',
            'SLA garantizado',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sectores/Industrias
    |--------------------------------------------------------------------------
    |
    | Categorías de industrias disponibles para empresas
    |
    */

    'industries' => [
        'health' => 'Salud y Medicina',
        'fintech' => 'Finanzas y Tecnología',
        'retail' => 'Retail y E-commerce',
        'education' => 'Educación',
        'real_estate' => 'Bienes Raíces',
        'hospitality' => 'Hospitalidad y Turismo',
        'technology' => 'Tecnología',
        'marketing' => 'Marketing y Publicidad',
        'media' => 'Medios y Entretenimiento',
        'government' => 'Gobierno y Sector Público',
        'nonprofit' => 'Organizaciones sin fines de lucro',
        'other' => 'Otro',
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates de Búsqueda por Industria
    |--------------------------------------------------------------------------
    |
    | Templates predefinidos de búsquedas según la industria
    |
    */

    'search_templates' => [
        'health' => [
            [
                'name' => 'Menciones Médicas - Argentina',
                'query' => 'min_replies:1 (medico OR clinica OR hospital OR salud) -filter:replies',
                'country' => 'AR',
                'lang' => 'es',
                'description' => 'Detecta menciones sobre servicios médicos y salud',
                'ia_prompt' => 'Analiza el tweet y determina si podría ser de interés para un médico o profesional de la salud. Busca menciones sobre turnos, atención médica, sistemas de gestión, historias clínicas, recetas, pacientes o experiencias en clínicas y hospitales.',
            ],
            [
                'name' => 'Quejas de Pacientes',
                'query' => '(mala atencion OR mal servicio OR queja) (clinica OR hospital OR medico)',
                'country' => 'AR',
                'lang' => 'es',
                'description' => 'Identifica quejas y comentarios negativos',
                'ia_prompt' => 'Evalúa si el tweet expresa una queja o experiencia negativa de un paciente respecto a la atención médica, clínicas, hospitales o médicos. Detecta emociones como enojo, frustración o decepción hacia el sistema de salud.',
            ],
        ],

        'retail' => [
            [
                'name' => 'Quejas de Clientes',
                'query' => 'min_replies:1 (queja OR reclamo OR problema OR mal servicio) -filter:replies',
                'country' => 'AR',
                'lang' => 'es',
                'description' => 'Detecta quejas y reclamos de clientes',
                'ia_prompt' => 'Analiza si el tweet refleja una experiencia negativa con un producto, tienda o servicio. Identifica quejas, reclamos, problemas de atención o fallas en la entrega o calidad.',
            ],
            [
                'name' => 'Menciones de Producto',
                'query' => '(compre OR comprando OR producto OR tienda)',
                'country' => 'AR',
                'lang' => 'es',
                'description' => 'Rastrea menciones de productos y compras',
                'ia_prompt' => 'Determina si el tweet menciona una experiencia de compra o el uso de un producto o marca. Busca comentarios sobre satisfacción, calidad o intención de compra.',
            ],
        ],

        'fintech' => [
            [
                'name' => 'Problemas con Pagos',
                'query' => '(no puedo pagar OR problema pago OR tarjeta rechazada)',
                'country' => 'AR',
                'lang' => 'es',
                'description' => 'Detecta problemas con métodos de pago',
                'ia_prompt' => 'Analiza si el tweet describe dificultades para realizar pagos o usar servicios financieros. Identifica menciones a tarjetas, transferencias, apps de pago o bancos que no funcionan correctamente.',
            ],
        ],

        'default' => [
            [
                'name' => 'Búsqueda Personalizada',
                'query' => '',
                'country' => 'AR',
                'lang' => 'es',
                'description' => 'Define tus propios términos de búsqueda',
                'ia_prompt' => 'Analiza el tweet según los términos personalizados definidos por el usuario. Evalúa su relevancia con respecto al contexto o industria que el usuario haya configurado.',
            ],
        ],


    ],
];
