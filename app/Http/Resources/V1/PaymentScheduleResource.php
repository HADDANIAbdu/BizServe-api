<?php

namespace App\Http\Resources\V1;

use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentScheduleResource extends JsonResource
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
            "payment_id" => $this->payment_id,
            "amount" => $this->amount,
            "due_date" => $this->due_date,
            "status" => $this->status,
        ];
    }
}
