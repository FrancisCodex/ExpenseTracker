<?php

namespace App\Http\Resources\Expenses;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'expense_id' => $this->expense_id,
        'title' => $this->title,
        'amount' => $this->amount,
        'entry_date' => $this->entry_date,
        'description' => $this->description,
        'user_id' => $this->user_id,
        'category' => $this->whenLoaded('category', function () {
            return $this->category->name;
        }),
    ];
}
}