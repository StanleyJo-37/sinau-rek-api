<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    //
    public function createProject(Request $request) {
        try {
            $request->validate([
                'title' => 'string|required|min:8',
                'description' => 'string',
                'start_time' => 'date|timezone',
                'deadline' => 'date|timezone',
            ]);
            
            DB::beginTransaction();

            $user = Auth::user();
            
            $project = Project::create([
                'title' => $request->title,
                'description' => $request->description ?? '',
                'start_time' => $request->start_time,
                'deadline' => $request->deadline,
            ]);

            ProjectUser::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return response()->json(
                [
                    'message' => 'Project created successfully.',
                    'project' => new ProjectResource($project),
                ],
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error creating project. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getProject(Request $request) {
        try {
            $request->validate([
                'project_id' => 'integer|required|exists:projects,id'
            ]);

            $user = Auth::user();

            $project = DB::table('projects', 'p')
                            ->select(
                                'p.*',
                            )
                            ->leftJoin(
                                'projects_users pu','pu.project_id', '=', 'p.id'
                            )
                            ->where([
                                ['pu.user_id', '=', $user->id],
                                ['p.id', '=', $request->project_id],
                            ])
                            ->first();

            if (empty($project) || ! isset($project)) {
                return response()->json(
                    [
                        'message' => 'You are not assigned to this project.',
                        'project' => null,
                    ],
                    403
                );
            }

            return response()->json(
                [
                    'message' => 'Project created successfully.',
                    'project' => new ProjectResource($project),
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error fetching project. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getAllProjects(Request $request) {
        try {
            $request->validate([

            ]);

            $user = Auth::user();

            $projects = DB::table('projects', 'p')
                            ->select(
                                'p.*',
                            )
                            ->leftJoin(
                                'projects_users pu','pu.project_id', '=', 'p.id'
                            )
                            ->where([
                                ['pu.user_id', '=', $user->id],
                            ])
                            ->get()
                            ->map(fn ($project) => new ProjectResource($project));

            return response()->json(
                [
                    'message' => 'Project created successfully.',
                    'projects' => $projects,
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error fetching projects. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function editProject(Request $request) {
        try {
            $request->validate([
                'project_id' => 'integer|required|exists:projects,id',
                'title' => 'string|required|min:8',
                'description' => 'string',
                'start_time' => 'date|timezone',
                'deadline' => 'date|timezone',
            ]);

            DB::beginTransaction();

            $user = Auth::user();

            $project = DB::table('projects', 'p')
                            ->select(
                                'p.*',
                            )
                            ->leftJoin(
                                'projects_users pu','pu.project_id', '=', 'p.id'
                            )
                            ->where([
                                ['pu.user_id', '=', $user->id],
                                ['p.id', '=', $request->project_id],
                            ])
                            ->first();

            $project = $project->update([
                'title' => $request->title,
                'description' => $request->description ?? '',
                'start_time' => $request->start_time,
                'deadline' => $request->deadline,
            ]);

            DB::commit();

            return response()->json(
                [
                    'message' => 'Project edited successfully.',
                    'projects' => new ProjectResource($project),
                ],
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error editing project. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function deleteProject(Request $request) {
        try {
            DB::beginTransaction();

            $request->validate([
                'project_id' => 'integer|required|exists:projects,id'
            ]);

            $user = Auth::user();
            
            $project = Project::findOrFail($request->project_id);
            
            $project->delete();

            DB::commit();

            return response()->json(
                [
                    'message' => 'Project deleted successfully.',
                ],
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error deleting project. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function assignTodo(Request $request) {
        try {
            $request->validate([
                'todo_id' => 'integer|required|exists:todos,id',
                'project_id' => 'integer|required|exists:projects,id',
            ]);

            $user = Auth::user();

        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error assigning todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function assignUser(Request $request) {
        try {
            $request->validate([
                'project_id' => 'integer|required|exists:projects,id',
                'user_id' => 'integer|required|exists:users,id'
            ]);

        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error assigning user. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function assignTeam(Request $request) {
        try {
            $request->validate([
                'project_id' => 'integer|required|exists:projects,id',
                'team_id' => 'integer|required|exists:teams,id'
            ]);

        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error assigning team. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
