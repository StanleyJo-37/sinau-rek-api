<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $s3 = Storage::disk('s3');
        if (!isset($this->teams) || empty($this->teams)) return [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_personal' => $this->personal_team,
            // 'team_photo_path' => $s3->url($this->team_photo_path),
            'team_photo_path' => $this->team_photo_path ?? "",
        ];
    }
}
