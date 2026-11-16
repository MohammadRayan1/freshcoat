<?php

namespace App\Http\Requests;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestMedia;
use App\Support\States;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', Rule::when($this->input('service_id') !== QuoteRequest::CUSTOM, ['exists:services,id'])],
            'custom_title' => ['nullable', 'required_if:service_id,'.QuoteRequest::CUSTOM, 'string', 'max:120'],
            'size' => ['nullable', 'required_unless:service_id,'.QuoteRequest::CUSTOM, 'integer', 'min:0', 'max:10'],
            'timing' => ['required', Rule::in(array_keys(QuoteRequest::TIMINGS))],
            'customer_supplies_paint' => ['nullable', 'boolean'],
            'city_id' => ['required', Rule::exists('cities', 'id')->whereIn('state_code', array_keys(States::launched()))],
            'zip' => ['required', 'digits:5'],
            'details' => ['nullable', 'string', 'max:2000'],
            'media' => ['nullable', 'array', 'max:'.QuoteRequestMedia::MAX_FILES],
            'media.*' => ['file', 'mimes:'.implode(',', QuoteRequestMedia::EXTENSIONS), 'max:'.QuoteRequestMedia::MAX_KILOBYTES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'zip.digits' => 'Enter a 5-digit ZIP code.',
            'city_id.required' => 'Pick the city where the job is.',
            'custom_title.required_if' => 'Tell painters what needs painting.',
            'media.max' => 'Attach up to '.QuoteRequestMedia::MAX_FILES.' photos or videos.',
            'media.*.mimes' => 'Photos must be JPG, PNG, WebP or HEIC, and videos MP4, MOV or WebM.',
            'media.*.max' => 'Each photo or video must be under '.(QuoteRequestMedia::MAX_KILOBYTES / 1024).' MB.',
        ];
    }
}
