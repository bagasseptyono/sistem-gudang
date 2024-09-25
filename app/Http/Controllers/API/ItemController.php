<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Http\Resources\MutationHistoryResource;
use App\Models\Item;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $items = Item::all();
            return $this->respondSuccess('Success Retrieve Items', ItemResource::collection($items));
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Retrieve Items', $th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'item_name' => 'required',
                'code' => 'required',
                'category' => 'required',
                'location' => 'required',
                'stock' => 'required',
                'description' => 'required',
            ]);

            Item::create($request->all());
            return $this->respondSuccess('Success Create Items');
        } catch (ValidationException $e) {
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Create Items', $th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $item = Item::find($id);

            if (!$item) {
                return $this->respondNotFound('Item Not Found');
            }


            return $this->respondSuccess('Success Retrieve Item', new ItemResource($item));
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Retrieve Item', $th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'item_name' => 'required',
                'code' => 'required',
                'category' => 'required',
                'location' => 'required',
                'stock' => 'required',
                'description' => 'required',
            ]);

            $item = Item::find($id);

            $stockDifference = $request->stock - $item->stock;

            if (!$item) {
                return $this->respondNotFound('Item Not Found');
            }

            $item->update($validated);

            if ($stockDifference != 0) {
                $mutationType = $stockDifference > 0 ? 'Masuk' : 'Keluar';
                $amount = abs($stockDifference);

                Mutation::create([
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                    'date' => now(),
                    'mutation_type' => $mutationType,
                    'amount' => $amount,
                ]);
            }

            return $this->respondSuccess('Success Update Items');
        } catch (ValidationException $e) {
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Update Items', $th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $item = Item::find($id);
            if (!$item) {
                return $this->respondNotFound('Item Not Found');
            }
            $item->delete();
            return $this->respondSuccess('Success Delete Item');
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error Delete Item', $th);
        }
    }

    public function mutationHistory($id)
    {
        try {
            $item = Item::with('mutations')->find($id);

            if (!$item) {
                return $this->respondInvalid('Item Not Found');
            }
    
            return $this->respondSuccess('Mutation history for item', MutationHistoryResource::collection($item->mutations));
           
        } catch (\Throwable $th) {
            return $this->respondInternalError('Error get Mutation history', $th);
        }
    }
}
