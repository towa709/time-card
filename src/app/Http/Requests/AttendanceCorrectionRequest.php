<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  /**
   * 🔹 breaks配列を break_start1, break_end1 ... に変換
   */
  protected function prepareForValidation()
  {
    if ($this->has('breaks')) {
      $mapped = [];

      foreach ($this->input('breaks') as $i => $break) {
        $index = $i + 1;
        $mapped["break_start{$index}"] = $break['start'] ?? null;
        $mapped["break_end{$index}"]   = $break['end'] ?? null;
      }

      // 🔹 変換結果をマージ
      $this->merge($mapped);

      // 🔹 元のbreaks配列を消しておく
      $this->offsetUnset('breaks');
    }
  }

  public function rules(): array
  {
    $rules = [
      'clock_in'  => ['nullable'],
      'clock_out' => ['nullable'],
      'note'      => ['required', 'string', 'max:255'],
    ];

    $breakKeys = collect($this->all())
      ->keys()
      ->filter(fn($key) => preg_match('/^break_(start|end)\d+$/', $key))
      ->map(fn($key) => (int)preg_replace('/\D/', '', $key))
      ->unique()
      ->sort()
      ->values();

    if ($breakKeys->isEmpty()) {
      $breakKeys = collect(range(1, 5));
    }

    foreach ($breakKeys as $i) {
      $requiredRule = ($i === 1) ? 'required' : 'nullable';
      $rules["break_start{$i}"] = [$requiredRule, 'regex:/^(?:[01]?\d|2[0-3]):[0-5]\d$/'];
      $rules["break_end{$i}"] = [$requiredRule, 'regex:/^(?:[01]?\d|2[0-3]):[0-5]\d$/'];
    }

    return $rules;
  }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      $clockIn = str_replace('：', ':', $this->input('clock_in'));
      $clockOut = str_replace('：', ':', $this->input('clock_out'));

      if ($clockIn && $clockOut) {
        $in = Carbon::createFromFormat('H:i', sprintf('%05s', $clockIn));
        $out = Carbon::createFromFormat('H:i', sprintf('%05s', $clockOut));

        if ($in->gte($out)) {
          $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
        }

        foreach ($this->keys() as $key) {
          if (preg_match('/^break_start(\d+)$/', $key, $m)) {
            $i = $m[1];
            $sVal = str_replace('：', ':', $this->input("break_start{$i}"));

            if ($sVal !== null && $sVal !== '') {
              try {
                $s = Carbon::createFromFormat('H:i', $sVal);
                if ($s->lt($in) || $s->gt($out)) {
                  $validator->errors()->add("break_start{$i}", '休憩時間が不適切な値です');
                }
              } catch (\Exception $e) {
              }
            }
          }

          if (preg_match('/^break_end(\d+)$/', $key, $m)) {
            $i = $m[1];
            $sVal = str_replace('：', ':', $this->input("break_start{$i}"));
            $eVal = str_replace('：', ':', $this->input("break_end{$i}"));

            if ($eVal !== null && $eVal !== '') {
              try {
                $e = Carbon::createFromFormat('H:i', $eVal);

                if ($sVal && $sVal !== '') {
                  $s = Carbon::createFromFormat('H:i', $sVal);

                  if ($e->gt($out) || $e->lte($s)) {
                    $validator->errors()->add("break_end{$i}", '休憩時間もしくは退勤時間が不適切な値です');
                  }
                } elseif ($e->gt($out)) {
                  $validator->errors()->add("break_end{$i}", '休憩時間もしくは退勤時間が不適切な値です');
                }
              } catch (\Exception $e) {
              }
            }
          }
        }
      }
    });
  }

  public function messages(): array
  {
    return [
      'clock_in.required'     => '出勤時間もしくは退勤時間が不適切な値です',
      'clock_in.regex'        => '出勤時間もしくは退勤時間が不適切な値です',
      'clock_out.required'    => '出勤時間もしくは退勤時間が不適切な値です',
      'clock_out.regex'       => '出勤時間もしくは退勤時間が不適切な値です',
      'break_start1.required' => '休憩時間が不適切な値です',
      'break_start1.regex'    => '休憩時間が不適切な値です',
      'break_end1.required'   => '休憩時間が不適切な値です',
      'break_end1.regex'      => '休憩時間が不適切な値です',
      'note.required'         => '備考を記入してください',
    ];
  }
}
