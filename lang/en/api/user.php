<?php

return [
    'create'    => [
        'success'           => 'User created successfully.',
        'failed'            => 'User registration failed.',
        'unverified'        => 'User has been registered, please verify account before logging in.',
    ],
    'register'  => [
        'success'           => 'User created successfully.',
        'failed'            => 'User registration failed.',
        'unverified'        => 'User has been registered, please verify account before logging in.',
    ],
    'update'    => [
        'failed'            => 'Failed to update user.',
        'success'           => 'User updated successfully.'
    ],
    'login'     => [
        'failed'                => 'Failed to login, please verify details.',
        'success'               => 'Logged in successfully.',
        'unverified'            => 'Your account is unverified, please check your phone to verify account.',
        'disabled'              => 'Your account is disabled, please contact :email for details.',
        'invalid_credentials'   => 'Invalid credentials provided.',
        'invalid_role'          => 'You are not authorized to access this area.'
    ],
    'logout'     => [
        'failed'                => 'Failed to logout.',
        'success'               => 'Logged out successfully.',
    ],
    'verification'  => [
        'failed'            => 'Failed to verify your account.',
        'success'           => 'Account is verified successfully.',
        'missing_fields'    => 'Required fields are missing.',
        'token_invalid'     => 'Invalid token provided.',
        'token_unavailable' => 'Token is not provided to verify account.',
        'already_verified'  => 'User account is already verified, please login.'
    ],
    'reset'     => [
        'failed'            => 'Failed to reset password.',
        'success'           => 'Password is updated successfully.',
        'initiate'          => 'User verified, password reset can be initiated now with OTP.'
    ],
    'delete'    => [
        'success'   => 'User deleted successfully.',
        'failed'    => 'Failed to delete user.'
    ],
];
