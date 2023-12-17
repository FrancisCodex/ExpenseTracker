<?php

namespace App\Http\Resources\Incomes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'income_id' => $this->income_id,
            'title' => $this->title,
            'amount' => $this->amount,
            'date' => $this->entry_date, // corrected field name
            'description' => $this->description,
            'user_id' => $this->user_id,
            'category' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
        ];
    }
}
