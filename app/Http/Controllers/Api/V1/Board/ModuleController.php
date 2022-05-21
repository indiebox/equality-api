<?php

namespace App\Http\Controllers\Api\V1\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Module\EnableKanbanModuleRequest;
use App\Http\Resources\V1\Board\ModuleCollection;
use App\Models\Board;
use App\Models\Module;
use App\Services\Contracts\Modules\KanbanService;

class ModuleController extends Controller
{
    public function __construct(
        protected KanbanService $kanban
    ) {
    }

    public function index(Board $board)
    {
        $modules = Module::all()->map(function ($module) use ($board) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'enabled' => $board->modules?->find($module->id) != null,
            ];
        });

        return new ModuleCollection($modules);
    }

    public function enableKanban(EnableKanbanModuleRequest $request, Board $board)
    {
        $this->kanban->enable($board, $request->validated());

        return response('', 204);
    }

    public function disableKanban(Board $board)
    {
        $this->kanban->disable($board);

        return response('', 204);
    }
}
