<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Http\Requests\StorepaymentRequest;
use App\Http\Requests\UpdatepaymentRequest;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Payment::all());
    }

    public function userPayment(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'payment_id' => ['required', 'exists:payments,id'],
            'account_name' => ['nullable'],
            'number' => ['required']
        ]);
        $user = User::find($request->user_id);
        $user->payments()->attach($request->payment_id, ['account_name' => $request->account_name, 'number' => $request->number]);

        return response()->json($user->fresh());
    }

    public function updateUserPayment(Request $request, User $user, Payment $payment, $number)
    {
        abort_if($user->payments()->where([
            'payment_id' => $payment->id,
            'number' => $number
        ])->doesntExist(), ResponseStatus::NOT_FOUND->value);
        $data = $request->validate([
            'account_name' => ['nullable'],
            'number' => ['required']
        ]);

        DB::table('user_payment')->where([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'number' => $number
        ])->update($data);

        return response()->json($user->payments()->where([
            'payment_id' => $payment->id,
            'number' => $request->number
        ])->first());
    }

    public function deleteUserPayment(User $user, Payment $payment, $number)
    {
        abort_if($user->payments()->where([
            'payment_id' => $payment->id,
            'number' => $number
        ])->doesntExist(), ResponseStatus::NOT_FOUND->value);


        DB::table('user_payment')->where([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'number' => $number
        ])->delete();

        return response()->json('ok');
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
     * @param  \App\Http\Requests\StorepaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepaymentRequest $request)
    {
        $data = $request->validated();
        $payment = Payment::create($data);
        return response()->json($payment, ResponseStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepaymentRequest  $request
     * @param  \App\Models\payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepaymentRequest $request, payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(payment $payment)
    {
        //
    }
}
