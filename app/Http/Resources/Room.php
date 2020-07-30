<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class Room extends JsonResource
{

    /**
     * @var
     */
    private $authenticated;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param $authenticated boolean is user authenticated (has valid access code, member or owner)
     */
    public function __construct($resource, $authenticated)
    {
        parent::__construct($resource);
        $this->authenticated = $authenticated;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'owner'             => $this->owner->firstname.' '.$this->owner->lastname,
            'type'              => new RoomType($this->roomType),
            'authenticated'     => $this->authenticated,
            'allowMembership'   => Auth::user() && $this->allowMembership,
            'isMember'          => $this->resource->isMember(Auth::user()),
            'isOwner'           => $this->owner->is(Auth::user()),
            'isGuest'           => Auth::guest(),
            'isModerator'       => $this->resource->isModeratorOrOwner(Auth::user()),
            'canStart'          => Gate::inspect('start', $this->resource)->allowed(),
            'running'           => $this->resource->runningMeeting() != null,
            'accessCode'        => $this->when($this->resource->isModeratorOrOwner(Auth::user()), $this->accessCode),
            'files'             => $this->when($this->authenticated, RoomFile::collection($this->resource->files()->where('download', true)->get()))
        ];
    }
}
