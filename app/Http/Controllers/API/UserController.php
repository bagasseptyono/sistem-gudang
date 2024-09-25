<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MutationHistoryResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::all();
            return $this->respondSuccess('Success Retrieve Users', UserResource::collection($users));
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Retrieve Users', $th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $userAuth = $request->user();
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone_number' => 'nullable|string',
                'address' => 'nullable|string',
                'role' => 'nullable|string',
            ]);
            if ($userAuth->role == 'admin') {
                $role = $request->role ?? 'user';
            } else {
                $role = 'user';
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'role' => $role,
            ]);

            return $this->respondSuccess('User created successfully', new UserResource($user));
        } catch (ValidationException $e) {
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error creating user', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->respondSuccess('User retrieved successfully', new UserResource($user));
        } catch (\Throwable $th) {
            return $this->respondNotFound('User not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $userAuth = $request->user();
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6',
                'phone_number' => 'nullable|string',
                'address' => 'nullable|string',
                'role' => 'nullable|string',
            ]);

            $user = User::find($id);
            if (!$user) {
                return $this->respondInvalid('User not found');
            }

            if ($userAuth->role != 'admin' || $userAuth->id == $user->id) {
                return $this->respondUnauthorized('Unauthorized, Only Admin / User that Logged in can Update');
            }

            if ($userAuth->role == 'admin') {
                $role = $request->role ?? 'user';
            } else {
                $role = 'user';
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'phone_number' => $request->phone_number ?? $user->phone_number,
                'address' => $request->address ?? $user->address,
                'role' => $role,
            ]);

            return $this->respondSuccess('User updated successfully', new UserResource($user));
        } catch (ValidationException $e) {
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error updating user', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($request->user()->role !== 'admin') {
                return $this->respondInvalid('Unauthorized to delete user');
            }

            $user->delete();
            return $this->respondSuccess('User deleted successfully');
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Delete Item', $th);
        }
    }


    public function mutationHistory($id)
    {
        try {
            $user = User::with('mutations')->find($id);
            if (!$user) {
                return $this->respondInvalid('User Not Found');
            }
    
            return $this->respondSuccess('Mutation history for user', MutationHistoryResource::collection($user->mutations));
           
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error get Mutation history', $th);
        }
    }
}
