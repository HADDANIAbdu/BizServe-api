<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReminderResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "client_id" => $this->client_id,
            "message" => $this->message,
            "date_created" => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
