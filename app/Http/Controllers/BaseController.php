<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class BaseController extends Controller
{

    public function sendResponse($result, $message, $paginate = null)
    {
        $response = [
            'status'  => 200,
            'message' => $message,
            'content' => $result,
        ];


        if ($paginate) {
            $response['paginate'] = [
                'current'   => $paginate->currentPage(),
                'per_page'  => $paginate->perPage(),
                'pages'     => $paginate->lastPage(),
                'total'     => $paginate->total(),
            ];
        }

        return response()->json($response, 200);
    }


    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }

    Public function utils(){
        return app()->make('utils');
    }
    protected function sendCustomLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $user->createToken('API Token')->plainTextToken, // if using Laravel Sanctum or Passport
            ]
        ], 200);
    }


    protected function success($message, $data): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => trans($message), // Translated message
            'data' => $data
        ], 200);
    }

    protected function notfound($message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => trans($message) // Translated message
        ], 404);
    }

    function validateFcmToken( $token ){
        $service    = Firebase::messaging();
        try {
            $validate = $service->validateRegistrationTokens( $token );
            if( isset( $validate['valid'] ) && in_array( $token, $validate['valid'] ) ){
                return true;
            }
        } catch (MessagingException $e) {} catch (FirebaseException $e) {}

        return false;
    }

}
