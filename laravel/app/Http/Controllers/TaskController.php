<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get the filter and sort parameters from the request
        $filter = $request->query('filter', ''); // Default to empty string if not provided
        $sort = $request->query('sort', ''); // Default to empty string if not provided

        // Query the tasks based on the user, filter, and sort parameters
        $query = Task::where("user_id", $user->id);

        // Apply filtering
        if ($filter === 'completed') {
            $query->where('status', 1);
        } elseif ($filter === 'in-progress') {
            $query->where('status', 0);
        }

        // Apply sorting
        if ($sort === 'date asc') {
            $query->orderBy('due_date', 'asc');
        } elseif ($sort === 'date desc') {
            $query->orderBy('due_date', 'desc');
        } elseif ($sort === 'title asc') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'title desc') {
            $query->orderBy('title', 'desc');
        }

        // Paginate the results and return as a collection
        $tasks = $query->paginate(5);

        return TaskResource::collection($tasks);
        // /////////////////////////////////////////
        // return TaskResource::collection(Task::where("user_id",$user->id)->paginate(5));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $result = Task::create($request->validated());

        return new TaskResource($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task , Request $request)
    {
        $user = $request->user();
        if ($user->id !== $task->user_id) {
            return abort(403 , "You Dont own the requested task");
        }
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {

        $task->update($request->validated());
        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task , Request $request)
    {
        $user = $request->user();
        if ($user->id !== $task->user_id) {
            return abort(403, "You dont own the requested Task");
        }
        $task->delete();
        return response("", 204);
    }
}