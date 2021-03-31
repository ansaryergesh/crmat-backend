<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class BankResource extends JsonResource
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
            'stavka' => $this->stavka,
            'approve_percent' => $this->approve_percent,
            'auction' => $this->auction,
            'sell_quantity' => $this->sell_quantity,
            'rate' => $this->rate,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'details' => BankDetailResource::collection(DB::table('bank_details')->where('bank_id',$this->id)->get()),
        ];
        return $array;
    }
}
