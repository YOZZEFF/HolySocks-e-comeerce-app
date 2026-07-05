<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{

        public function __construct(

            private UserService $userService
        ){ }
    public function index( Request $request){


        $users = $this->userService->getUsers($request);

        return response()->json([
            'status'     => true,
            'data'       => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'from'         => $users->firstItem(),
                'to'           => $users->lastItem(),
            ],
        ],200);
    }


    public function show($id){

    $user = $this->userService->getUser($id);

     if(!$user){

        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ],404);
    }

    return response()->json([
        'status' => true,
        'data'   => new UserResource($user),
    ],200);
    }
  private  function findUser($id){

  $user = $this->userService->findUser($id);


    if(!$user){

        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ],404);

    }

    return $user;

    }
    public function block($id){

     $user =  $this->userService->block($id);

      if (!$user) {
        return response()->json([
            'status'  => false,
            'message' => 'User not found',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'User blocked successfully',
    ],200);
    }

    public function unblock($id){


     $user = $this->userService->unblock($id);
      if (!$user) {
        return response()->json([
            'status'  => false,
            'message' => 'User not found',
        ], 404);
    }
    return response()->json([
        'status' => true,
        'message' => 'User unblocked successfully',
    ],200);

    }
}
