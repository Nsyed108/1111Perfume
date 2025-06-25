<?php

namespace App\Http\Controllers\Api;

use App\Actions\ValidatePermission;
use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use App\Http\Requests\User\UserUpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Requests\User\UserVerifyRequest;
use Botble\ACL\Http\Requests\CreateUserRequest;
use Illuminate\Http\Request;
use App\Services\LoginUserService;

class UserController extends Controller {
    /**
     * @param UserStoreRequest $request
     * @param User $users
     * @return Response
     */
    public function store( CreateUserRequest $request, Customer $users ){
        $user           = $users->create([
            'active'        => true,
            'first_name'          => $request->input('first_name'),
            'last_name'         => $request->input('last_name'),
            'email'         => $request->input('email'),
            'password'      => $request->input('password' )
        ]);

        if( $user->id > 0 )
        {

            $store->assignRole( $request->roles );

            // save device token
            if( $request->has('device_token') && utils()->validateFcmToken( $request->device_token ) ){
                $user->devices()->updateOrCreate( ['token' => $request->device_token ] );
            }

            return $this->created( 'api/user.register.unverified', $user );
        }

        return $this->error( 'api/user.register.failed' );
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

    /**
     * @param UserLoginRequest $request
     * @param LoginUserService $loginUserService
     * @return Response
     */
    public function login( UserLoginRequest $request, LoginUserService $loginUserService ){
        // setup login service
        $service    = $loginUserService->setup( $request->login, $request->password );

        // user must be active
        if( !$service->isActiveUser() ){
            return $this->error( 'api/user.login.inactive', $service->getSafeUser() );
        }

        // user must be verified
        if( !$service->isVerifiedUser() ){
            return $this->error( 'api/user.login.unverified', $service->getSafeUser() );
        }

        // now that all is well lets authenticate
        $login  = $service->loginApi()->saveDevice( $request->input('device_token') );
        return $login
                    ? $this->success( 'api/user.login.success', $service->getSafeUser() )
                    : $this->error('api/user.login.failed', $service->getSafeUser(), [ 'email' => config('api.contact_email')] );
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

            return $this->success( 'api/user.logout.success' );
        }
        return $this->error( 'api/user.logout.failed' );
    }

}
