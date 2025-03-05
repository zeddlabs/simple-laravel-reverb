<?php

namespace App\Http\Controllers;

use App\Events\TodoCreated;
use App\Events\TodoDeleted;
use App\Events\TodoUpdated;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index()
    {
        return response()->json(Todo::all());
    }

    public function store(Request $request)
    {
        $todo = Todo::create([
            'title' => $request->title
        ]);
        broadcast(new TodoCreated($todo))->toOthers();
        return response()->json($todo, 201);
    }

    public function update(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);
        $todo->update($request->all());
        broadcast(new TodoUpdated($todo))->toOthers();
        return response()->json($todo);
    }

    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);
        $todo->delete();
        broadcast(new TodoDeleted($todo))->toOthers();
        return response()->json(null, 204);
    }
}
