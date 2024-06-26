<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\UpdateReceiptRequest;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'order_in' => ['in:desc,asc'],
            'customer_phone' => ['string'],
            'customer_name' => ['string'],
            'date' => ['date'],
            'code' => ['']
            // 'code' => [function ($attribute, $value, $fail) {
            //     $order = Receipt::find(Receipt::codeToId($value));
            //     if (!$order || $value != $order->code) $fail('The ' . $attribute . ' is invalid.');
            // },]
        ]);
        $receipts = Receipt::of($request->user())->filter($request->only(['order_in', 'customer_phone', 'customer_name', 'date', 'code']))->paginate($request->per_page ?? 10);
        return response()->json($receipts);
    }

    public function all(Request $request)
    {
        return response()->json($request->user()->receipts);
    }

    public function allByUser(User $user)
    {
        return response()->json($user->receipts);
    }


    public function getKnownCustomers(Request $request)
    {
        $user = $request->user();

        // $customers = Cache::rememberForever($user->id . "knownUsers", fn () => Receipt::whereBelongsTo($user)->orderBy('id', 'desc')->get(['customer_name', 'customer_address', 'customer_phone']));
        $customers = Receipt::whereBelongsTo($user)->orderBy('id', 'desc')->get(['customer_name', 'customer_address', 'customer_phone']);
        return response()->json($customers->map(fn ($value) => [
            'name' => $value->customer_name,
            'mobile' => $value->customer_phone,
            'address' => $value->customer_address,
        ])->unique('mobile')->values());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReceiptRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReceiptRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        abort_if($user->isAdmin(), ResponseStatus::UNAUTHORIZED->value, "Admin cannot add receipt");
        $data['user_id'] = $user->id;
        $receipt = Receipt::create($data);
        $items = [];
        foreach ($data['items'] as $value) {
            $item = Item::query()->where([
                'user_id' => $user->id,
                'name' => $value['name']
            ])->first();

            if ($item) {
                $item->price = $value['price'];
                $item->save();
            } else {
                $item = $user->items()->create($value);
            }

            $item->quantity = $value['quantity'];
            $items[] = $item;
        }
        foreach ($items as $item) {
            $receipt->items()->attach($item->id, [
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);
        }
        return response()->json($receipt->fresh(), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Receipt $receipt)
    {
        abort_unless($request->user()->id == $receipt->user->id, ResponseStatus::NOT_FOUND->value);
        return response()->json($receipt);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReceiptRequest  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReceiptRequest $request, Receipt $receipt)
    {
        $data = $request->validated();
        $user = $request->user();
        $receipt = DB::transaction(function () use ($receipt, $data, $user) {
            $receipt->update([
                'date' => $data['date'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'discount' => $data['discount'] ?? 0,
                'deposit' => $data['deposit'] ?? 0,
                'note' => $data['note'] ?? '',
                'status' => $data['status']
            ]);

            $items = [];
            foreach ($data['items'] as $value) {
                $item = Item::query()->where([
                    'user_id' => $user->id,
                    'name' => $value['name']
                ])->first();

                if ($item) {
                    $item->price = $value['price'];
                    $item->save();
                } else {
                    $item = $user->items()->create($value);
                }

                $item->quantity = $value['quantity'];
                $items[] = $item;
            }

            $receipt->items()->detach();
            $receipt->items()->attach(collect($items)->mapWithKeys(function ($item) {
                return [$item->id => ['price' => $item->price, 'quantity' => $item->quantity]];
            }));
            return $receipt->fresh();
        });

        return response()->json($receipt->load(['user']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receipt $receipt)
    {
        //
    }
}
