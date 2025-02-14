<?php

namespace App\Http\Resources\V1;

use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
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
            "total_amount" => $this->total_amount,
            "payment schedules" => PaymentScheduleResource::collection($this->paymentSchedules),
        ];
    }
}
