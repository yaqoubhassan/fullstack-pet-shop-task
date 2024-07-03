<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;

class UserCollection extends ResourceCollection
{
    protected $token;

    public function __construct($resource, $token = null)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($resource) use ($request) {
                return (new UserResource($resource, $this->token))->toArray($request);
            }),
        ];
    }
}
