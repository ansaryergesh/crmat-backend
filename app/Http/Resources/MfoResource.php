<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $array = [
          'id' => $this->id,
          'name' => $this->name,
          'logo' => $this->logo,
          'amount_min' => $this->amount_min,
          'amount_max' => $this->amount_max,
          'srok_min' => $this->srok_min,
          'srok_max' => $this->srok_max,
          'approve_percent' => $this->approve_percent,
          'review_time' => $this->review_time,
          'stavka' => $this->stavka,
        ];

        return $array;
    }
}
