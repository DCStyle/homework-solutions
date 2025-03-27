<?php

namespace App\Http\Requests\Wiki;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\CommentRepository;
use Illuminate\Support\Facades\App;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content' => 'required|string|min:5',
            'parent_id' => 'nullable|exists:wiki_comments,id',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if user is rate limited for comments
            $commentRepository = App::make(CommentRepository::class);

            if ($commentRepository->shouldModerateUser()) {
                $validator->errors()->add(
                    'content',
                    'You have reached the maximum number of comments allowed per day.'
                );
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'content.required' => 'Nội dung bình luận không được để trống.',
            'content.min' => 'Nội dung bình luận phải có ít nhất :min ký tự.',
            'parent_id.exists' => 'Bình luận gốc không tồn tại.'
        ];
    }
}
