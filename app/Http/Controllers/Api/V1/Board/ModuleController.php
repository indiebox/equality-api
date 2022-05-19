<?php

namespace App\Http\Controllers\Api\V1\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Module\EnableKanbanModuleRequest;
use App\Models\Board;
use App\Services\Contracts\Boards\ModuleService;

class ModuleController extends Controller
{
    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    public function enableKanban(EnableKanbanModuleRequest $request, Board $board)
    {
        $this->moduleService->enableKanban($board, $request->validated());

        return response('', 204);
    }

    public function disableKanban(Board $board)
    {
        $this->moduleService->disableKanban($board);

        return response('', 204);
    }
}
