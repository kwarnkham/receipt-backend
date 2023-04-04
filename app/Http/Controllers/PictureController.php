<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Http\Requests\StorePictureRequest;
use App\Http\Requests\UpdatePictureRequest;
use App\Models\Picture;
use Illuminate\Http\Request;
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
        $data['name'] = Storage::putFile("/users", $data['picture'], 'public');
        abort_unless($data['name'], ResponseStatus::SERVER_ERROR->value, 'Failed to upload to S3 storage');

        $picture = Picture::where([
            'user_id' => $data['user_id'],
            'type' => $data['type']
        ])->first();
        if ($picture) {
            $picture->deleteFromCloud();
            $picture->name = $data['name'];
            $picture->save();
        } else {
            $picture = Picture::create($data);
        }
        return response()->json($picture, ResponseStatus::CREATED->value);
    }

    public function storePublic(Request $request)
    {
        $request->validate(['picture' => ['required', 'image']]);

        return response()->json($request->file('picture')->store('public/print'));
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
