<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  protected function prepareForValidation()
  {
    if ($this->has('breaks')) {
      $mapped = [];

      foreach ($this->input('breaks') as $i => $break) {
        $index = $i + 1;
        $mapped["break_start{$index}"] = $break['start'] ?? null;
        $mapped["break_end{$index}"] = $break['end'] ?? null;
      }

      $this->merge($mapped);
    }
  }

  public function rules(): array
  {
    $rules = [
      'clock_in'  => ['required', 'date_format:H:i'],
      'clock_out' => ['required', 'date_format:H:i'],
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
      $rules["break_start{$i}"] = [$requiredRule, 'date_format:H:i'];
      $rules["break_end{$i}"] = [$requiredRule, 'date_format:H:i'];
    }

    return $rules;
  }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      $clockIn = $this->input('clock_in');
      $clockOut = $this->input('clock_out');

      if ($clockIn && $clockOut) {
        $in = Carbon::createFromFormat('H:i', $clockIn);
        $out = Carbon::createFromFormat('H:i', $clockOut);

        if ($in->gte($out)) {
          $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
        }

        foreach ($this->keys() as $key) {
          if (preg_match('/^break_start(\d+)$/', $key, $m)) {
            $i = $m[1];
            $startVal = $this->input("break_start{$i}");

            if ($startVal !== null && $startVal !== '') {
              try {
                $breakStart = Carbon::createFromFormat('H:i', $startVal);
                if ($breakStart->lt($in) || $breakStart->gt($out)) {
                  $validator->errors()->add("break_start{$i}", '休憩時間が不適切な値です');
                }
              } catch (\Exception $e) {
              }
            }
          }
        }

        foreach ($this->keys() as $key) {
          if (preg_match('/^break_end(\d+)$/', $key, $m)) {
            $i = $m[1];
            $startVal = $this->input("break_start{$i}");
            $endVal = $this->input("break_end{$i}");

            if ($endVal !== null && $endVal !== '') {
              try {
                $bEnd = Carbon::createFromFormat('H:i', $endVal);
                if ($startVal && $startVal !== '') {
                  $bStart = Carbon::createFromFormat('H:i', $startVal);
                  if ($bEnd->gt($out) || $bEnd->lte($bStart)) {
                    $validator->errors()->add("break_end{$i}", '休憩時間もしくは退勤時間が不適切な値です');
                  }
                } elseif ($bEnd->gt($out)) {
                  $validator->errors()->add("break_end{$i}", '休憩時間もしくは退勤時間が不適切な値です');
                }
              } catch (\Exception $e) {
              }
            }
          }
        }
      }

      if (!$this->input('note')) {
        $validator->errors()->add('note', '備考を記入してください');
      }
    });
  }

  public function messages(): array
  {
    return [
      'clock_in.required'     => '出勤時間を入力してください',
      'clock_out.required'    => '退勤時間を入力してください',
      'note.required'         => '備考を記入してください',
      'break_start1.required' => '休憩開始時間を入力してください',
      'break_end1.required'   => '休憩終了時間を入力してください',
    ];
  }
}
