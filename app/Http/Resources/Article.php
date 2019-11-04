<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Article extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' =>   [
                'id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'created_at' => $this->created_at->format('M d, Y'),
                'last_updated_at' => $this->updated_at->diffForHumans(),
            ],
            'links' =>  [
                'self' => $this->path(),
            ]
        ];
    }
}
