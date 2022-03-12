<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Project;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            $project->boards()->save(Board::factory()->make());
        }
    }
}
