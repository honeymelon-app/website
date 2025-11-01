<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtifactRequest;
use App\Http\Requests\UpdateArtifactRequest;
use App\Models\Artifact;

class ArtifactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArtifactRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Artifact $artifact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Artifact $artifact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArtifactRequest $request, Artifact $artifact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Artifact $artifact)
    {
        //
    }
}
