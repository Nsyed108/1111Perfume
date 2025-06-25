<?php


namespace App\Http\Controllers\Api;

use App\Helpers\Response;
// use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\UsersProfile\UsersProfileUpdateRequest;
use App\Models\Customer;

class CustomerProfileController extends BaseController {

    public function show( UsersProfile $usersProfile ){
        $usersProfiles   = $usersProfile->with('user')
                            ->where( 'user_id', utils()->getUserId() )
                            ->get();

        if ($usersProfiles->isNotEmpty()) {
            $profile = $usersProfiles->first();

            // Modify user role before returning
            if ($profile->user) {
                $profile->user->makeHidden(['roles','permissions', 'stripe_id', 'remember_token']);
                $profile->user->role = $profile->user->roles->pluck('name')->first() ?? '';
            }

            return $this->success('api/users_profile.read.success', $profile);
        }

        return $this->notfound( 'api/api/users.read.not_found' );
    }

    /**
     * @param UsersProfileUpdateRequest $request
     * @param UsersProfile $usersProfile
     * @return Response
     */
    public function update( UsersProfileUpdateRequest $request, UsersProfile $usersProfiles ){

        $usersProfile   = $usersProfiles
                            ->updateOrCreate(
                                [ 'user_id' => utils()->getUserId() ],
                                $request->validated()
                            );

        if( $usersProfile ){
            if( $request->has('image') ){
                $usersProfile->uploadImage( $request->file('image') );
            }

            return $this->success( 'api/users_profile.update.success' );
        }

        return $this->error( 'api/users_profile.update.failed' );
    }
}
