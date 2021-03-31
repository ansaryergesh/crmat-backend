<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankDetailResource extends JsonResource
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
          'bank_id' => $this->id,
          'description' => $this->description,
          'background_img' => $this->background_img,
          'address'=> $this->address,
          'email' => $this->email,
          'phone' => $this->phone,
          'documents' => $this->documents,
          'pension' => $this->pension,  
        ];

        return $array;
    }
}
