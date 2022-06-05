<?php

namespace App\Services\Modules;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\ColumnType;
use App\Models\Module;
use App\Services\Contracts\Modules\KanbanService as KanbanServiceContract;
use Illuminate\Support\Facades\DB;

class KanbanService implements KanbanServiceContract
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

    public function getSettings(Board $board)
    {
        if (!$board->modules()->where('module_id', Module::KANBAN)->exists()) {
            abort(403, "Kanban module is disabled.");
        }

        $columns = $board->columns()->kanbanRelated()->get();

        return collect(static::$availableColumns)->map(fn() => 0)->merge($columns->flatMap(function ($column) {
            $index = array_search($column->column_type_id, static::$availableColumns);

            return [$index => $column->id];
        }));
    }

    public function enable(Board $board, array $settings)
    {
        DB::transaction(function () use ($board, $settings) {
            $board->modules()->syncWithoutDetaching(Module::KANBAN);

            $this->setupKanbanModule($board, $settings);
        });
    }

    public function disable(Board $board)
    {
        DB::transaction(function () use ($board) {
            $board->modules()->detach(Module::KANBAN);

            Column::kanbanRelated()->update(['column_type_id' => ColumnType::NONE]);
        });
    }

    public function canMoveCardToColumn(Card $card, Column $column)
    {
        $order = $this->getColumnsMovementOrder($column->board);

        // Kanban module is disabled (no any columns with functional types).
        if (count($order) == 1 && $order[0] == ColumnType::NONE) {
            return true;
        }

        $oldColumn = $card->column;
        $index = array_search($column->column_type_id, $order);

        $allowedTypes = [];
        if ($index == 0) {
            $allowedTypes[] = $order[$index + 1];
        } elseif ($index == count($order) - 1) {
            $allowedTypes[] = $order[$index - 1];
        } else {
            $allowedTypes[] = $order[$index - 1];
            $allowedTypes[] = $order[$index + 1];
        }

        $allowedTypes[] = $order[$index];

        return in_array($oldColumn->column_type_id, $allowedTypes);
    }

    /**
     * Setup the kanban module with settings.
     * @param Board $board
     * @param array $settings
     */
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

    /**
     * Create the new kanban-related column.
     * @param Board $board
     * @param integer $columnType
     */
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

    /**
     * Get the order of movement columns.
     * @param Board $board
     * @return array
     */
    protected function getColumnsMovementOrder(Board $board)
    {
        $baseOrder = collect([
            ColumnType::TODO,
            ColumnType::IN_PROGRESS,
            ColumnType::ON_REVIEW,
            ColumnType::DONE,
        ]);

        $columns = $board->columns()->kanbanRelated()->pluck('column_type_id');

        return $baseOrder->intersect($columns)->prepend(ColumnType::NONE)->values()->toArray();
    }
}
