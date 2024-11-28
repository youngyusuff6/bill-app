<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function success($message = null, $data = null, $error_code = 200) {
        return response()->json([
            'status' => "success",
            'message' => $message,
            'data' => $data
        ], $error_code);
    }

    protected function fail($message = null, $data = null, $error_code = 422) {
        return response()->json([
            'status' => "failed",
            'message' => $message,
            'data' => $data
        ], $error_code);

    }
}
