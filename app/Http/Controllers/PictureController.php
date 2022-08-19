<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Http\Requests\StorePictureRequest;
use App\Http\Requests\UpdatePictureRequest;
use App\Models\Picture;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StorePictureRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePictureRequest $request)
    {
        $data = $request->validated();
        $data['name'] = Storage::disk('s3')->putFile(config('app')['name'] . "/users", $data['picture'], 'public');
        abort_unless($data['name'], ResponseStatus::SERVER_ERROR->value, 'Failed to upload to S3 storage');
        $picture = Picture::create($data);
        return response()->json($picture, ResponseStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function show(Picture $picture)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function edit(Picture $picture)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePictureRequest  $request
     * @param  \App\Models\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePictureRequest $request, Picture $picture)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function destroy(Picture $picture)
    {
        //
    }
}
