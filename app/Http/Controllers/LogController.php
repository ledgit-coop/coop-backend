<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogRequest;
use App\Http\Requests\LogSaveRequest;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index(LogRequest $request) {

        $logs = Log::on();

        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 30;

        if($request->module)
            $logs->where(function($logs)  use($request) {
                
                $logs->where('model', $request->module)
                ->where('model_id', $request->module_id);

                // Checks if parent from other logs
                $logs->orWhere(function($logs) use($request) {
                    $logs->where('parent_model', $request->module)
                    ->where('parent_model_id', $request->module_id);
                });
            });

        if(!empty($filters)) {
            if(isset($filters->keyword))
                $logs->where('content', 'like', "%$filters->keyword%");
        }
        if($request->sortField && $request->sortOrder)
            $logs->orderBy($request->sortField, $request->sortOrder);
        else
            $logs->orderBy('created_at', 'desc');

        $logs->with('user');

        return response()->json($logs->paginate($limit));
    }

    public function store(LogSaveRequest $request)
    {
        $model = ($request->module)::findOrFail($request->module_id);

        if($request->parent_module)
            ($request->parent_module)::findOrFail($request->parent_module_id);

        $log = Log::create([
            'type' => $request->type,
            'content' => $request->content,
            'model' => $request->module,
            'model_id' => $model->id,
            'parent_model' => $request->parent_module,
            'parent_model_id' => $request->parent_module_id,
            'created_by' => Auth::user()->id,
        ]);

        return response()->json($log);
    }

    public function show(Log $log)
    {
        return response()->json($log);
    }

    public function update(LogSaveRequest $request, Log $log)
    {
        ($request->module)::findOrFail($request->module_id);

        $log->content = $request->content;
        $log->save();

        return response()->json($log);
    }

    public function destroy(Log $log)
    {
        $log->delete();
        return response(true);
    }
}
