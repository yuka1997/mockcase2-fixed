<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'requested_clock_in'  => ['required', 'date_format:H:i'],
            'requested_clock_out' => ['required', 'date_format:H:i'],
            'requested_breaks.*.start' => ['nullable', 'date_format:H:i'],
            'requested_breaks.*.end'   => ['nullable', 'date_format:H:i'],
            'requested_note'      => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'requested_note.required' => '備考を記入してください。',
            'requested_clock_in.date_format'  => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_breaks.*.start.date_format' => '休憩時間が勤務時間外です',
            'requested_breaks.*.end.date_format'   => '休憩時間が勤務時間外です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = Carbon::createFromFormat('H:i', $this->input('requested_clock_in'));
            $clockOut = Carbon::createFromFormat('H:i', $this->input('requested_clock_out'));

            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('requested_breaks', []);
            foreach ($breaks as $index => $break) {
                if (!empty($break['start'])) {
                    $breakStart = Carbon::createFromFormat('H:i', $break['start']);
                    if ($breakStart->lt($clockIn) || $breakStart->gt($clockOut)) {
                        $validator->errors()->add("requested_breaks.$index.start", '休憩時間が勤務時間外です');
                    }
                }

                if (!empty($break['end'])) {
                    $breakEnd = Carbon::createFromFormat('H:i', $break['end']);
                    if ($breakEnd->lt($clockIn) || $breakEnd->gt($clockOut)) {
                        $validator->errors()->add("requested_breaks.$index.end", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}
