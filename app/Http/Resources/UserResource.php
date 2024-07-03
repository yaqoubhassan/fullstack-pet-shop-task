<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected $token;
    protected $isUser;

    public function __construct($resource, $token = null, $isUser = false)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->isUser = $isUser;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'is_marketing' => $this->is_marketing,
            'updated_at' => $this->updated_at->toISOString(),
            'created_at' => $this->created_at->toISOString()
        ];

        if ($this->isUser) {
            $data['email_verified_at'] = $this->email_verified_at;
            $data['last_login_at'] = $this->last_login_at;
        }

        if ($this->token !== null) {
            $data['token'] = $this->token;
        }

        return $data;
    }
}
