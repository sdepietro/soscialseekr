<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('l5-swagger.documentations.'.$documentation.'.api.title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }

        *, *:before, *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>

<body>
<div id="swagger-ui"></div>
<div id="toast-container" style="
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
"></div>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
<script>
    window.onload = function () {

        function getStoredToken() {
            return localStorage.getItem('jwt_token');
        }

        // Función para guardar el token en localStorage y aplicarlo en Swagger
        function saveToken(token) {
            if (token) {
                const bearerToken = `bearer ${token}`;

                // Estructura esperada por L5-Swagger
                const authObject = {
                    apiAuth: {
                        name: "apiAuth",
                        schema: {
                            type: "apiKey",
                            description: "Para obtener este token hay que hacer login con usuario y contraseña. Y se agregará una cabezcera AuthorizationJWT: Bearer xxxxxx.yyyyyy.zzzzzzzzzz. NO OLVIDARSE DE AGREGAR EL bearer a mano.",
                            name: "Authorizationjwt",
                            in: "header",
                            bearerFormat: "JWT",
                            scheme: "bearer"
                        },
                        value: bearerToken
                    }
                };

                // Guardar en localStorage
                localStorage.setItem('authorized', JSON.stringify(authObject));
                console.log("✅ Nuevo token guardado correctamente en localStorage para L5-Swagger:", authObject);

                // Aplicar token en Swagger UI
                setTimeout(() => {
                    console.log("🔄 Aplicando token en Swagger...");
                    ui.preauthorizeApiKey('apiAuth', bearerToken);
                    mostrarToast("✅ Login ya guardado en Swagger.");


                }, 1000);
            } else {
                console.error("❌ Error: No se recibió un token válido.");
            }
        }


        // Recuperar token almacenado en localStorage
        // const storedToken = localStorage.getItem('jwt_token');
        //
        // console.log("🔍 Token almacenado en localStorage:", storedToken);

        // Construcción de Swagger UI
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: "{!! $urlToDocs !!}",
            operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
            configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
            validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
            oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",
            requestInterceptor: function (request) {
                request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                request.headers['accept'] = 'application/json';
                return request;
            },
            responseInterceptor: function (response) {
                try {
                    if (!response || !response.url || !response.text) {
                        return response;
                    }

                    if (response.url.includes("v1/auth/login") && response.status === 201) {
                        let responseData = response.body || response.data;

                        // Si responseData es un string, intentar parsearlo
                        if (typeof responseData === "string") {
                            try {
                                responseData = JSON.parse(responseData);
                            } catch (error) {
                                console.error("❌ Error al parsear JSON de la respuesta:", error);
                                return response;
                            }
                        }

                        console.log("📥 Interceptando respuesta:", responseData);

                        // Extraer el token correctamente
                        const token = responseData?.data?.token;
                        if (token) {
                            console.log("✅ Token recibido en respuesta:", token);
                            saveToken(token); // Guardar y aplicar token automáticamente
                        } else {
                            console.warn("⚠️ No se encontró el token en la respuesta de login.");
                        }
                    }
                } catch (error) {
                    console.error("❌ Error en responseInterceptor:", error);
                }

                return response;
            },


            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout",
            docExpansion: "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
            deepLinking: true,
            filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
            persistAuthorization: true,
        });

        // Aplicar el token almacenado si existe
        // if (storedToken) {
        //     console.log("Token recuperado de localStorage:", storedToken);
        //     ui.preauthorizeApiKey('Bearer', storedToken);
        // } else {
        //     console.log("No hay token almacenado en localStorage");
        // }

        window.ui = ui;


        function mostrarToast(mensaje) {
            const toastContainer = document.getElementById('toast-container');

            // Crear el toast
            const toast = document.createElement('div');
            toast.innerText = mensaje;
            toast.style.cssText = `
                        background: #4CAF50;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 5px;
                        box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
                        font-size: 14px;
                        font-weight: bold;
                        animation: fade-in 0.3s ease-out;
                        opacity: 1;
                        transition: opacity 0.3s ease-out;
                    `;

            // Agregar al contenedor
            toastContainer.appendChild(toast);

            // Eliminar después de 5 segundos
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

// Animación CSS para suavizar la aparición del toast
        const style = document.createElement('style');
        style.innerHTML = `
                    @keyframes fade-in {
                        from { opacity: 0; transform: translateY(-10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    `;
        document.head.appendChild(style);

    };
</script>
</body>
</html>
