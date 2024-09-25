<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MutationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'user'  => [
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
                "phone_number" => $this->user->phone_number,
            ],
            'item' => [
                "id" => $this->item->id,
                "item_name" => $this->item->item_name,
                "code" => $this->item->code,
                "category" => $this->item->category,
                "location" => $this->item->location,
                "stock" => $this->item->stock,
                "description" => $this->item->description,
            ],
            'date' => $this->date,
            'mutation_type' => $this->mutation_type,
            'amount' => $this->amount,
        ];
    }
}
