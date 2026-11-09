<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required','max:255'],
            'email'     => ['required','email','unique:users,email'],
            'password'      => ['required'],
            'school_id' => ['required'],
            // ,'exists:schools,id'
            'class_id'  => ['required'],
            // ,'exists:classes,id'
        ];
    }

    #[Override]
    public function messages():array
    {
        return [
            
        'required' => ':attributeは必須です。',
        'integer'  => ':attributeは数値である必要があります。',
        'string'   => ':attributeは文字列である必要があります。',
        'max'      => ':attributeは:max文字以内である必要があります。',
        'digits'   => ':attributeは:digits桁である必要があります。',
        'email'=> '正しいメールアドレスである必要があります。',
        'digits_between'=> ':attributeは:min桁から:max桁である必要があります',
        'regex'=>':attributeは数値のみで入力してください',
        'exists'=>'その:attributeは存在しません',
        'unique'=>'その:attributeは既に使用されています'
        ];
    }
    #[Override]
    public function attributes():array
    {
        return ['name' => '名前','email'=>'メールアドレス','password'=>'パスワード','school_id'=>'所属校','classes'=>'クラス' ];
    }
}
