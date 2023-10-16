<?php

namespace App\Http\Controllers;

use App\Constants\Pagination;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request) {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;
        
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

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed', // Add 'confirmed' rule
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']); // Hash the password

        $user = User::create($validatedData);

        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed', // Add 'confirmed' rule
        ]);

        if ($request->filled('password')) {
            $validatedData['password'] = bcrypt($validatedData['password']); // Hash the password
        } else {
            unset($validatedData['password']); // Remove password field if not provided
        }

        $user->update($validatedData);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        
        return response(true);
    }
}
