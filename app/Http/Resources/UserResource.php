<?php

namespace App\Http\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $s3 = Storage::disk('s3');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // 'profile_photo_path' => $s3->url($this->profile_photo_path),
            'profile_photo_path' => $this->profile_photo_path ?? "",
            'token' => $this->token,
            'teams' => new TeamResource($this->teams),
        ];
    }
}
