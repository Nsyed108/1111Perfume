<?php


namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class BaseRequest extends FormRequest {
    protected function failedValidation( Validator $validator ){
        throw new HttpResponseException( utils()
            ->response()
            ->status( 'error' )
            ->items( $validator->errors() )
            ->submit( $this->getResponseMessage(), $this->getResponseReplacers() )
        );
    }

    protected function getResponseReplacers(){
        return [];
    }

    protected function getResponseMessage(){
        return 'api/general.failed';
    }

    public function authorize(){
        return true;
    }
}
