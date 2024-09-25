<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MutationResource;
use App\Models\Item;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MutationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            $mutations = Mutation::with(['item', 'user'])->orderBy('date', 'desc')->get();
            return $this->respondSuccess('Success Retrieve Mutations', MutationResource::collection($mutations));
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Retrieve Mutations', $th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $validatedData = $request->validate([
                'item_id' => 'required|exists:items,id',
                'date' => 'required|date',
                'mutation_type' => 'required|in:Masuk,Keluar',
                'amount' => 'required|integer|min:1',
            ]);

            $item = Item::find($request->item_id);
            if (!$item) {
                return $this->respondInvalid('Item not found');
            }
            DB::beginTransaction();

            if ($request->mutation_type == 'Masuk') {
                $item->stock += $request->amount;
            } elseif ($request->mutation_type === 'Keluar') {
                if ($item->stock < $request->amount) {
                    return $this->respondInvalid('Insufficient stock for this mutation');
                }
                $item->stock -= $request->amount;
            }
            $item->save();
            Mutation::create([
                'user_id' => $user->id,
                'item_id' => $request->item_id,
                'date' => $request->date,
                'mutation_type' => $request->mutation_type,
                'amount' => $request->amount,
            ]);
            DB::commit();
            return $this->respondSuccess('Success Create Mutation');
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->respondInternalError('Error Create Mutation', $th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $mutation = Mutation::find($id);
            if (!$mutation) {
                return $this->respondInvalid('Mutation not found');
            }
            return $this->respondSuccess('Success Retrieve Mutation', new MutationResource($mutation));
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Retrieve Mutation', $th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $user = $request->user();
            $request->validate([
                'item_id' => 'required|exists:items,id',
                'date' => 'required|date',
                'mutation_type' => 'required|in:Masuk,Keluar',
                'amount' => 'required|integer|min:1',
            ]);

            $mutation = Mutation::find($id);
            $item = Item::find($request->item_id);
            if (!$mutation || !$item) {
                return $this->respondInvalid('Mutation or Item not found');
            }
            DB::beginTransaction();

            if ($mutation->mutation_type == 'Masuk') {
                $item->stock -= $mutation->amount;
            } elseif ($mutation->mutation_type == 'Keluar') {
                $item->stock += $mutation->amount;
            }

            if ($request->mutation_type == 'Masuk') {
                $item->stock += $request->amount;
            } elseif ($request->mutation_type == 'Keluar') {
                $item->stock -= $request->amount;
            }

            $item->save();
            $mutation->update($request->all());

            DB::commit();
            return $this->respondSuccess('Mutation updated successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->respondInternalError('Error Create Mutation', $th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $mutation = Mutation::find($id);
            if (!$mutation) {
                return $this->respondNotFound('Mutation Not Found');
            }
            $item = Item::findOrFail($mutation->item_id);

            if ($mutation->mutation_type == 'Masuk') {
                $item->stock -= $mutation->amount;
            } elseif ($mutation->mutation_type == 'Keluar') {
                $item->stock += $mutation->amount;
            }
            $item->save();
            $mutation->delete();

            return $this->respondSuccess('Success Delete Mutation');
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Delete Mutation', $th);
        }
    }
}
