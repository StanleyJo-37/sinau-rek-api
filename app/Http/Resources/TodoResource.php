<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodoResource extends JsonResource
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
            'todo_title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'deadline' => $this->deadline,
            'project_id' => $this->project_id,
            'project_name' => $this->project_title,
            'team_id' => $this->team_id,
            'team_name' => $this->team_name,
            'is_done' => $this->is_done,
        ];
    }
}
