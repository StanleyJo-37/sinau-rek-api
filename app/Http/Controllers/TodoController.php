<?php

namespace App\Http\Controllers;

use App\Http\Resources\TodoResource;
use App\Models\Team;
use App\Models\Todo;
use App\Models\TodoProject;
use App\Models\TodoUser;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    //

    public function getAllTodos(Request $request)
    {
        try {
            $request->validate([
                'team_id' => 'integer',
                'project_id' => 'integer',
            ]);

            $user = Auth::user();

            $todos = DB::select("
                SELECT
                    t.id,
                    t.title title,
                    t.description,
                    t.start_time,
                    t.deadline,
                    t.project_id,
                    p.title project_title,
                    te.id team_id,
                    te.name team_name
                FROM todos t
                LEFT JOIN todos_users tou ON tou.todo_id = t.id
                LEFT JOIN team_user tu ON tu.user_id = tou.user_id AND tu.team_id = :team_id
                LEFT JOIN teams te ON te.id = tu.team_id
                LEFT JOIN projects p ON p.id = t.project_id AND p.id = :project_id
                WHERE tou.user_id = :user_id;
            ", [
                'user_id' => $user->id,
                'team_id' => $request->team_id,
                'project_id' => $request->project_id,
            ]);

            foreach($todos as $todo) {
                $todo->start_time = $todo->start_time ?? Carbon::parse($todo->start_time);
                $todo->deadline = $todo->deadline ?? Carbon::parse($todo->deadline);

                $todo = new TodoResource($todo);
            }

            return response()->json($todos);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error fetching todos. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getTodo(int $todo_id, Request $request)
    {
        try {
            $request->validate([
                'team_id' => 'integer',
                'project_id' => 'integer',
            ]);

            $user = Auth::user();

            $todo = DB::select("
                SELECT
                    t.id,
                    t.title title,
                    t.description,
                    t.start_time,
                    t.deadline,
                    t.project_id,
                    p.title project_title,
                    te.id team_id,
                    te.name team_name,
                    tou.is_done
                FROM todos t
                LEFT JOIN todos_users tou ON tou.user_id = :user_id
                LEFT JOIN team_user tu ON tu.user_id = tou.user_id AND tu.team_id = :team_id
                LEFT JOIN teams te ON te.id = tu.team_id
                LEFT JOIN projects p ON p.id = t.project_id AND p.id = :project_id
                WHERE t.id = :todo_id;
            ", [
                'user_id' => $user->id,
                'team_id' => $request->team_id,
                'project_id' => $request->project_id,
                'todo_id' => $todo_id,
            ]);

            if (! empty($todo) && isset($todo[0])) {
                $todo = $todo[0];
            } else {
                return response()->json(
                    ['message' => 'Todo not found.'],
                    404,
                );
            }

            return response()->json(new TodoResource($todo));
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error fetching todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function createTodo(Request $request)
    {
        try {
            $request->validate([
                'title' => 'string|required',
                'description' => 'string|required',
                'start_time' => 'date',
                'deadline' => 'date',
                'project_id' => 'integer|nullable',
            ]);

            DB::beginTransaction();
            
            $user = Auth::user();

            $start_time = $request->start_time ? Carbon::parse($request->start_time)->setTimezone('UTC')->toDayDateTimeString() : null;
            $deadline = $request->deadline ? Carbon::parse($request->deadline)->setTimezone('UTC')->toDayDateTimeString() : null;

            $now = Carbon::now();

            $todo = Todo::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_time' => $start_time,
                'deadline' => $deadline,
                'project_id' => $request->project_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $todo_user = TodoUser::create([
                'todo_id' => $todo->id,
                'user_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            return response()->json(new TodoResource($todo));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error creating todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function editTodo(Request $request, int $todo_id)
    {
        try {
            $request->validate([
                'title' => 'string|required',
                'description' => 'string|required',
                'start_time' => 'date',
                'deadline' => 'date',
                'project_id' => 'integer',
            ]);
            
            $user = Auth::user();

            DB::beginTransaction();

            $start_time = $request->start_time ? Carbon::parse($request->start_time)->setTimezone('UTC')->toDayDateTimeString() : null;
            $deadline = $request->deadline ? Carbon::parse($request->deadline)->setTimezone('UTC')->toDayDateTimeString() : null;

            $now = Carbon::now();

            $todo = Todo::firstOrFail($todo_id);
            $todo->title = $request->title;
            $todo->description = $request->description;
            $todo->start_time = $start_time;
            $todo->deadline = $deadline;
            $todo->updated_at = $now;
            $todo->save();

            DB::commit();

            return response()->json(new TodoResource($todo));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error creating todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function deleteTodo(int $todo_id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $todo = Todo::firstOrFail($todo_id);
            $todo->delete();

            DB::commit();

            return response()->json(new TodoResource($todo));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error deleting todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function assignUsers(Request $request)
    {
        try {
            $request->validate([
                'assignees' => 'required|array',
                'assignees.*.id' => 'required|integer|exists:users,id',
                'assignees.*.priority' => 'integer|nullable',
                'todo_id' => 'required|integer|exists:todos,id',
            ]);

            DB::beginTransaction();

            $now = Carbon::now();
            
            foreach($request->assignees as $assignee) {
                TodoUser::create([
                    'todo_id' => $request->todo_id,
                    'user_id' => $assignee->id,
                    'priority' => $assignee->priority,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();

            return response()->json('Users assigned.');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error assigning users. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function assignTeam(Request $request)
    {
        try {
            $request->validate([
                'todo_id' => 'required|integer|exists:todos,id',
                'team_id' => 'required|integer|exists:teams,id',
                'priority' => 'required|integer|min:1|max:3',
            ]);

            DB::beginTransaction();

            $user_ids = User::whereHas('teams', function ($query) use($request) {
                $query->where('team_id', $request->team_id);
            })->pluck('id')->toArray();

            $now = Carbon::now();
            
            foreach($user_ids as $user_id) {
                TodoUser::create([
                    'todo_id' => $request->todo_id,
                    'user_id' => $user_id,
                    'priority' => $request->priority,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();

            return response()->json('Team assigned.');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error assigning team. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function updateTodoCompletion(Request $request)
    {
        try {
            $request->validate([
                'todo_id' => 'required|integer|exists:todos,id',
                'new_status' => 'boolean',
            ]);

            DB::beginTransaction();

            $user = Auth::user();

            $todo = TodoUser::where([
                ['todo_id', $request->todo_id],
                ['user_id', $user->id],
            ])->firstOrFail();

            $todo->update([ 'is_done' => ! $request->new_status ?? true ]);

            DB::commit();

            return response()->json('Todo completion update successful.');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Error updating todo completion. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

}
