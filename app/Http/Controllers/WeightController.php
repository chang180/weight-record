<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\weight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeightController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(Auth::user()->id);
        $user_id = Auth::user()->id;
        $weights = weight::where('user', $user_id)
            ->orderBy('record_at', 'DESC')
            ->paginate(15);
        // dd($weights);
        return view('record', ['weights' => $weights]);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // dd($request->input(),$_POST);
        $weight = new weight;
        $weight->record_at = $request->input('record_at');
        $weight->weight = $request->input('weight');
        $weight->user = $request->input('user');
        $weight->save();

        return redirect('/dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function show(weight $weight)
    {
        $user_id = Auth::user()->id;
        $weights = weight::where('user', $user_id)
            ->orderBy('record_at', 'ASC')
            ->get();
        return view('chart', ['weights' => $weights]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // dd($request);
        $weight = weight::find($id);
        $weight->record_at = $request->input('record_at');
        $weight->weight = $request->input('weight');
        $weight->user = $request->input('user');
        $weight->save();
        return redirect('/record');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, weight $weight)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        weight::destroy($id);
        return redirect('/record');
    }
}
