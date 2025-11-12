<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use function App\Helpers\wGetConfigs;

class WabotyService
{

    var $waboty_client_id = null;
    var $waboty_client_secret = null;
    var $waboty_url = null;


    public function __construct()
    {
        $this->waboty_client_id = config('constants.waboty.client_id');
        $this->waboty_client_secret = config('constants.waboty.client_secret');
        $this->waboty_url = "https://api.waboty.com/";
    }


    public function sendWhatsapp($phone, $message, $bot_id = null, $template = null, $template_params = [])
    {

        if (empty($phone)) {
            return (object)[
                'status' => 'error',
                'message' => 'El número de teléfono no puede estar vacío.'
            ];
        }
        if (empty($message)) {
            return (object)[
                'status' => 'error',
                'message' => 'El mensaje no puede estar vacío.'
            ];
        }

        if (!$this->isValidArgMobileIntl($phone)) {
            return (object)[
                'status' => 'error',
                'message' => 'El número de teléfono no es válido. Tiene que comenzar con 9 y tener 11 dígitos en total.',
            ];

        }

        $webhookUrl = config('constants.slack_notification_webhook');
        $this->waboty_url = "https://api.waboty.com/";

        $client = new Client();

        try {

            $response = $client->request('POST', $this->waboty_url . "v1/messages", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Waboty-client-id' => $this->waboty_client_id,
                    'Waboty-client-secret' => $this->waboty_client_secret,
                ],
                'form_params' => [
                    'phone' => $phone,
                    'text' => $message,
                    'bot_id' => $bot_id,
                    'template' => $template,
                    'template_params' => (object)$template_params,
                ]
            ]);

            $response = json_decode($response->getBody()->getContents());

            if (empty($response->data->status)) {
                Http::post($webhookUrl, [
                    'attachments' => [
                        [
                            'color' => 'danger', // Rojo
                            'text' => 'Hubo un problema con el envío de Whatsapp. El estado del envío quedó en: *' . $response->data->status . '*',
                            'fields' => [
                                [
                                    'title' => 'Response',
                                    'value' => print_r($response, true),
                                    'short' => false
                                ],
                                [
                                    'title' => 'Phone',
                                    'value' => $phone,
                                    'short' => true
                                ]
                            ]
                        ]
                    ]
                ]);
                return (object)[
                    'status' => 'error',
                    'message' => 'No se envío el mensaje de Whatsapp. Problemas en el servicio de mensajería de Whatsapp.',
                ];
            }

            if ($response->data->status != "sent") {
                Http::post($webhookUrl, [
                    'attachments' => [
                        [
                            'color' => 'danger', // Rojo
                            'text' => 'Hubo un problema con el envío de Whatsapp. El estado del envío quedó en: *' . $response->data->status . '*',
                            'fields' => [
                                [
                                    'title' => 'Response',
                                    'value' => print_r($response, true),
                                    'short' => false
                                ],
                                [
                                    'title' => 'Phone',
                                    'value' => $phone,
                                    'short' => true
                                ]
                            ]
                        ]
                    ]
                ]);
                return (object)[
                    'status' => 'error',
                    'message' => 'No se envío el mensaje de Whatsapp. El estado del envío quedó en: ' . $response->data->status,
                ];

            }

            return (object)[
                'status' => 'success',
                'message' => 'Mensaje enviado correctamente.',
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura excepciones de errores 4xx
            $response = $e->getResponse();
            // Obtener el cuerpo de la respuesta como string
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);
            return (object)[
                'status' => 'error',
                'message' => 'Error al enviar el mensaje de Whatsapp: ' . $responseData['message' ?? $responseBody],
            ];

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Captura excepciones de errores 5xx
            $response = $e->getMessage();
            $webhookUrl = config('constants.slack_notification_webhook');
            Http::post($webhookUrl, [
                'text' => 'Hubo un problema con el envío de Whatsapp. Número:' . $phone . ' Response: ' . $response,
                'username' => 'Sistema de Alertas',
                'icon_emoji' => ':robot_face:',
                'mrkdwn' => false,
            ]);
            return (object)[
                'status' => 'error',
                'message' => 'Error al enviar el mensaje de Whatsapp: ' . $response,
                'username' => 'Sistema de Alertas',
                'icon_emoji' => ':robot_face:',
                'mrkdwn' => false,
            ];

        } catch (\Exception $e) {
            // Captura cualquier otra excepción que pueda ocurrir.
            $response = $e->getMessage();
            $webhookUrl = config('constants.slack_notification_webhook');
            Http::post($webhookUrl, [
                'text' => 'Hubo un problema con el envío de Whatsapp. Número:' . $phone . ' Response: ' . $response,
                'username' => 'Sistema de Alertas',
                'icon_emoji' => ':robot_face:',
                'mrkdwn' => false,
            ]);
            return (object)[
                'status' => 'error',
                'message' => 'Error al enviar el mensaje de Whatsapp: ' . $response,
            ];

        }

    }


    public function sendPdf($phone, $message, $pdf)
    {

        if (empty($phone)) {
            return false;
        }
        if (empty($message)) {
            return false;
        }
        if (empty($pdf)) {
            return false;
        }
        $this->waboty_url = "https://api.waboty.com/";

        $client = new Client();
        try {
            $response = $client->request('POST', $this->waboty_url . "v1/sendpdf", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Waboty-client-id' => $this->waboty_client_id,
                    'Waboty-client-secret' => $this->waboty_client_secret,
                ],
                'form_params' => [
                    'phone' => $phone,
                    'text' => $message,
                    'pdf' => $pdf
                ]
            ]);

            $response = json_decode($response->getBody()->getContents());
            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura excepciones de errores 4xx
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = $response->getBody()->getContents();
            //ToDo_Woopi: Loguear el errorBody en algun lado

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Captura excepciones de errores 5xx
            // Maneja los errores de servidor de manera similar si es necesario.
        } catch (\Exception $e) {
            // Captura cualquier otra excepción que pueda ocurrir.
            // Puedes manejarla de acuerdo a tus necesidades.
        }

    }

    function isValidArgMobileIntl($number): bool
    {
        if (empty($number)) {
            return true; // Si el número está vacío, consideramos que es válido.
        }
        //Si el numerp no arranca con 54, entonces es de otro pais, asi que no lo validmos
        if (strpos($number, '54') !== 0) {
            return true; // No es un número argentino, así que no lo validamos.
        }


        // 1. Eliminamos espacios, guiones y paréntesis para dejar solo dígitos y “+”.
        $clean = preg_replace('/[\s\-\(\)]/', '', $number);

        // 2. Comprobamos el patrón: opcional “+”, luego 549 y diez dígitos.
        return preg_match('/^\+?549\d{10}$/', $clean) === 1;
    }


}
