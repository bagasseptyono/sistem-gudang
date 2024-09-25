<?php

namespace App\Http\Controllers;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    public function respond($message = 'Success', $data = null, $statusCode = 200, $headers = [])
    {
        if ($data instanceof JsonResource) {
            return $data->additional(compact('message'))
                ->response()
                ->setStatusCode($statusCode);
            // ->withHeaders($headers);
        }
        $status = "" . $statusCode;
        $respondData = compact('data', 'message', 'status');
        return response()->json($respondData, $statusCode, $headers);
    }

    public function respondSuccess($message = null, $data = null, $headers = [])
    {
        $message = $message ?? __('response.200');
        return $this->respond($message, $data, 200, $headers);
    }

    public function respondCreated($message = null, $data = null, $headers = [])
    {
        $message = $message ?? __('response.201');
        return $this->respond($message, $data, 201, $headers);
    }

    public function respondNoContent($message = ' ', $headers = [])
    {
        return $this->respond($message, null, 204, $headers);
    }

    public function respondInvalid($message = null, $error = null, $headers = [])
    {
        $message = $message ?? __('response.400');
        return $this->respond($message, $error, 400, $headers);
    }

    public function respondUnauthorized($message = null, $error = null, $headers = [])
    {
        $message = $message ?? __('response.401');
        return $this->respond($message, $error, 401, $headers);
    }

    public function respondForbidden($message = null, $error = null, $headers = [])
    {
        $message = $message ?? __('response.403');
        return $this->respond($message, $error, 403, $headers);
    }

    public function respondNotFound($message = null, $error = null, $headers = [])
    {
        $message = $message ?? __('response.404');
        return $this->respond($message, $error, 404, $headers);
    }

    public function respondInternalError($message = null, $error = null, $headers = [])
    {
        $message = $message ?? __('response.500');
        return $this->respond($message, $error, 500, $headers);
    }
}
