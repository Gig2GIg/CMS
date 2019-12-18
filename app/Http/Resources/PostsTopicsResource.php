<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\Topics;

use App\Http\Repositories\TopicsRepository;

class PostsTopicsResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $topicRepo = new TopicsRepository(new Topics());
        $topic = $topicRepo->find($this->topic_id);
        

        return [
            'id' => $topic->id,
            'title' => $topic->title
        ];
    }
}
