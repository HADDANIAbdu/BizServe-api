<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'client' => [
                'id' => $this->client->id,
                'firstname' => $this->client->firstname,
                'lastname' => $this->client->lastname
            ],
            'service' => [
                'id' => $this->service->id,
                'name' => $this->service->name,
            ],
            'scheduled_at' => $this->scheduled_at,
            'type' => $this->type,
        ];
    }
}
