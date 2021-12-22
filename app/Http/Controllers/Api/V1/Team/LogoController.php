<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreLogoRequest;
use App\Http\Resources\V1\Team\TeamResource;
use App\Models\Team;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoController extends Controller
{
    public function store(StoreLogoRequest $request, Team $team) {
        $directory = "teams/logo/" . date('m.Y');
        Storage::makeDirectory($directory);

        $file = $request->file('logo');
        $fileName = $this->generateLogoName($file);
        $path = $file->storeAs($directory, $fileName);

        if ($team->logo != null) {
            Storage::delete($team->logo);
        }

        $team->logo = $path;
        $team->save();

        return new TeamResource($team);
    }

    public function destroy(Team $team) {
        if ($team->logo != null) {
            Storage::delete($team->logo);

            $team->logo = null;
            $team->save();
        }

        return new TeamResource($team);
    }

    protected function generateLogoName(UploadedFile $file)
    {
        return Str::random(40) . "." . Str::lower($file->getClientOriginalExtension());
    }
}
