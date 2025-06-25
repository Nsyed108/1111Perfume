<?php

namespace App\Http\Controllers\Api;

use App\Actions\ValidatePermission;
use App\Helpers\Response;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use App\Http\Requests\User\UserUpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Requests\User\UserVerifyRequest;
use Botble\ACL\Http\Requests\CreateUserRequest;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\Customer;
use App\Services\LoginUserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Botble\Ecommerce\Http\Requests\CustomerCreateRequest;
use Botble\Ecommerce\Http\Requests\CustomerLoginRequest;
use Botble\ACL\Traits\AuthenticatesUsers;
use Botble\ACL\Traits\LogoutGuardTrait;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Ecommerce\Facades\EcommerceHelper;

class CustomerController extends BaseController {

    use AuthenticatesUsers, LogoutGuardTrait {
        AuthenticatesUsers::attemptLogin as baseAttemptLogin;
    }

    /**
     * @param UserStoreRequest $request
     * @param User $users
     * @return Response
     */
    public function store(CustomerCreateRequest $request)
    {
        $data = $request->validated();
        $data['confirmed_at'] = now();
        $data['dob'] = Carbon::parse($request->input('dob'));

        $customer = new Customer();
        $customer->fill($data)->save();

        if ($customer->id > 0) {

            // save device token
            if( $request->has('device_token') && utils()->validateFcmToken( $request->device_token ) ){
                $customer->devices()->updateOrCreate( ['token' => $request->device_token ] );
            }
            event(new CreatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));
            return $this->sendResponse($customer, "Registered successfully.");
        } else {
            return $this->sendError("Something went wrong", [], 505);
        }
    }

    /**
     * @param UserUpdateRequest $request
     * @param User $users
     * @return Response
     */
    public function update( UserUpdateRequest $request, User $users ){
        $user   = $users->where( 'id', utils()->getUserId() )->get();
        if( $user->first()->update( $request->validated() ) ){
            return $this->success( 'api/user.update.success' );
        }

        return $this->error( 'api/user.update.failed' );
    }

    public function show( Customer $usersProfile ){

        $userId = auth()->id();


        $usersProfiles   = $usersProfile->where( 'id', $userId )
                            ->get();

        if ($usersProfiles->isNotEmpty()) {
            return $this->sendResponse($usersProfiles, "usersProfiles listed successfully.");
        } else {
            return $this->sendError("No brands found", [], 404);
        }

    }


    public function login(CustomerLoginRequest  $request)
    {
        // Manually fetch the user
        $user = Customer::where('email', $request->email)->first();

        // Check if user exists and password matches
        if ($user && Hash::check($request->password, $user->password)) {
            // Auth success - create token or session as needed
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => $user,
                    'token' => $user->createToken('API Token')->plainTextToken,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
        ], 401);
    }


    /**
     * @param UserVerifyRequest $request
     * @param LoginUserService $loginUserService
     * @param User $user
     * @return Response
     */
    public function verify( UserVerifyRequest $request, LoginUserService $loginUserService, User $user ){
        $phoneNumber    = utils()->getPhoneFromFbaToken( $request->token );
        $user           = $user->where( 'login', $phoneNumber )->get();
        if( $phoneNumber && $user->isNotEmpty() ){
            $user   = $user->first();
            $login  = $loginUserService->setUser( $user )->setVerified()->loginApi();

            return $this->success( 'api/user.verification.success', $login->getSafeUser() );
        }

        return $this->error( 'api/user.verification.failed' );
    }

    /**
     * @param UserResetPasswordRequest $request
     * @return Response
     */
    public function resetPassword( UserResetPasswordRequest $request ){
        return $this->success( 'api/user.reset.initiate' );
    }

    /**
     * @param UserUpdatePasswordRequest $request
     * @param User $user
     * @return Response
     */
    public function updatePassword( UserUpdatePasswordRequest $request, User $user ){
        $login  = utils()->getPhoneFromFbaToken( $request->token );
        if( $login ){
            $users  = $user->where( 'login', $login )->get();
            if( $users->isNotEmpty() ){
                if( $users->first()->updatePassword( $request->password ) ){
                    return $this->success( 'api/user.reset.success' );
                }
            }
        }

        return $this->error('api/user.reset.failed');
    }

    public function destroy( User $users ){
        $user       = $users->find( auth()->id() );
        ValidatePermission::authorizeApi( $user, 'delete' );

        return $user->id != config('general.permanent_super_admin') && $user->safeDelete()
                        ? $this->success( 'api/user.delete.success' )
                        : $this->error( 'api/user.delete.failed' );
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();

            return $this->sendResponse([], "Logged out successfully.");
        }

        return $this->sendError("Failed to logout.", [], 404);
    }

}
