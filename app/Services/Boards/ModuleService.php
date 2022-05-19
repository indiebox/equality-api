<?php

namespace App\Services\Boards;

use App\Models\Board;
use App\Models\Column;
use App\Models\ColumnType;
use App\Models\Module;
use App\Services\Contracts\Boards\ModuleService as ModuleServiceContract;
use Illuminate\Support\Facades\DB;

class ModuleService implements ModuleServiceContract
{
    /**
     * Available column types.
     * The key is the key of column from settings, the value is a column type.
     * @var array<string,int>
     */
    public static $availableColumns = [
        'todo_column_id' => ColumnType::TODO,
        'inprogress_column_id' => ColumnType::IN_PROGRESS,
        'done_column_id' => ColumnType::DONE,
        'onreview_column_id' => ColumnType::ON_REVIEW,
    ];

    public function enableKanban(Board $board, array $settings)
    {
        DB::transaction(function () use ($board, $settings) {
            $board->modules()->syncWithoutDetaching(Module::KANBAN);

            $this->setupKanbanModule($board, $settings);
        });
    }

    public function disableKanban(Board $board)
    {
        DB::transaction(function () use ($board) {
            $board->modules()->detach(Module::KANBAN);

            Column::kanbanRelated()->update(['column_type_id' => ColumnType::NONE]);
        });
    }

    protected function setupKanbanModule(Board $board, array $settings)
    {
        $columns = array_intersect_key($settings, static::$availableColumns);

        // Reset column types only for columns that are not in our settings array.
        $ids = collect($columns)->values()->filter(fn($value) => $value instanceof Column)->pluck('id');
        Column::kanbanRelated()->whereNotIn('id', $ids)->update(['column_type_id' => ColumnType::NONE]);

        foreach ($columns as $name => $column) {
            $columnType = static::$availableColumns[$name];

            if ($column instanceof Column) {
                $column->columnType()->associate($columnType);
                $column->save();
            } elseif ($column === 0) {
                $this->createColumn($board, $columnType);
            }
        }
    }

    protected function createColumn(Board $board, int $columnType)
    {
        $columnName = match ($columnType) {
            ColumnType::TODO => "To Do",
            ColumnType::IN_PROGRESS => "In Progress",
            ColumnType::DONE => "Done",
            ColumnType::ON_REVIEW => "On Review",
        };

        $column = new Column(['name' => $columnName]);
        $column->columnType()->associate($columnType);
        $column->board()->associate($board);
        $column->moveToEnd();
    }
}
