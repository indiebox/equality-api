<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreImageRequest;
use App\Http\Resources\V1\Project\ProjectResource;
use App\Models\Project;
use App\Services\Image\Contracts\ImageServiceContract;

class ImageController extends Controller
{
    protected $image;

    public function __construct(ImageServiceContract $imageService)
    {
        $this->image = $imageService;
    }

    public function store(StoreImageRequest $request, Project $project) {
        $directory = "projects/image/" . date('m.Y');

        $path = $this->image->save($request->file('image'), $directory);
        $this->image->delete($project->image);

        $project->image = $path;
        $project->save();

        return new ProjectResource($project);
    }

    public function destroy(Project $project) {
        if ($this->image->delete($project->image)) {
            $project->image = null;
            $project->save();
        }

        return new ProjectResource($project);
    }
}
