<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InteractionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            "client" => [
                "id" => $this->client->id,
                "firstname" => $this->client->firstname,
                "lastname" => $this->client->lastname,
                "email" => $this->client->email,
            ],
            "service" => [
                "id" => $this->service->id,
                "name" => $this->service->name,
            ],
            'type' => $this->type,
            'date_interaction' => $this->date_interaction,
            'outcome' => $this->outcome,
            'details' => $this->details
        ];
    }
}
