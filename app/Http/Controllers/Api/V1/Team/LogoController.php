<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreLogoRequest;
use App\Http\Resources\V1\Team\TeamResource;
use App\Models\Team;
use App\Services\Image\Contracts\ImageServiceContract;

class LogoController extends Controller
{
    protected $image;

    public function __construct(ImageServiceContract $imageService)
    {
        $this->image = $imageService;
    }

    public function store(StoreLogoRequest $request, Team $team) {
        $directory = "teams/logo/" . date('m.Y');

        $path = $this->image->save($request->file('logo'), $directory);
        $this->image->delete($team->logo);

        $team->logo = $path;
        $team->save();

        return new TeamResource($team);
    }

    public function destroy(Team $team) {
        if ($this->image->delete($team->logo)) {
            $team->logo = null;
            $team->save();
        }

        return new TeamResource($team);
    }
}
