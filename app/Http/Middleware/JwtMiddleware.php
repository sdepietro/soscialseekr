<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

//use Tymon\JWTAuth\JWTAuth;

class JwtMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // echo "<pre>"; print_r($headers); die();
        try {
            //Por problemas con NutHost tenemos que pisar la cabecera Authorization por authorizationjwt
            $headers = \Request::header();

            if (!empty($headers['authorizationjwt'])) {
                $request->headers->set('Authorization', $headers['authorizationjwt'], true);// set header in request
            }

            if (!empty($headers['Authorizationjwt'])) {
                $request->headers->set('Authorization', $headers['Authorizationjwt'], true);// set header in request
            }

            $user = JWTAuth::parseToken()->authenticate();
            if (empty($user)) {
                $errorMessage = "El usuario fue eliminado. Por favor inicie sesión con otro usuario..";
                return response()->json([
                    'status' => false,
                    'errors' => [$errorMessage],
                    'message' => $errorMessage
                ], 401);
            }
            // dd($user);
        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                $errorMessage = "Debe iniciar sesión nuevamente.";
                return response()->json([
                    'status' => false,
                    'errors' => [$errorMessage],
                    'message' => $errorMessage
                ], 401);


            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                $errorMessage = "El token expiró.";
                return response()->json([
                    'status' => false,
                    'errors' => [$errorMessage],
                    'message' => $errorMessage
                ], 401);
            } else {
                $errorMessage = "No se encontró el token del usuario.";
                return response()->json([
                    'status' => false,
                    'errors' => [$errorMessage],
                    'message' => $errorMessage
                ], 401);
            }
        }
        return $next($request);
    }


}
