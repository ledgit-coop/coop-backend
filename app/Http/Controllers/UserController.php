<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request) {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
        
        $users = User::select([
            'id',
            'email', 
            'name',
            'created_at',
        ]);
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $users->where(function($users) use($filters) {
                    $users->orWhere('name', 'like', "%$filters->keyword%")
                    ->orWhere('email', 'like', "%$filters->keyword%");
                });
        }

        if($request->sortField && $request->sortOrder)
            $users->orderBy($request->sortField, $request->sortOrder);


        return $users->paginate($limit);
    }
}
