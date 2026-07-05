<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {




    }

    public function getUsers(Request $request){

     $users = User::query()

                        ->when($request->name , fn ($q) =>
                                $q->where('name' , 'like', '%' . $request->name . '%') )

                        ->when($request->email , fn ($q) =>
                                $q->where('email' , 'like', '%' . $request->email . '%'))

                        ->when($request->has('is_active') , fn ($q) =>
                                $q->where('is_active' , $request->is_active))

                        ->paginate(10);

                        return $users ;
    }

    public function getUser($id){

    return   User::with('orders')->find($id);


    }
    public function findUser($id){

     return  User::find($id);

    }

    public function block($id){

     $user = $this->findUser($id);

     if (!$user) return null;

    $user->update(['is_active' => false]);

    return $user;
    }

    public function unblock($id){

    $user = $this->findUser($id);

    if (!$user) return null;

    $user->update(['is_active' => true]);

    return $user;

    }
}
