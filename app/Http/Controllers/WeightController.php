<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Weight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeightController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $query = Weight::where('user', $user_id);

        // 處理日期範圍篩選
        if ($request->has('start_date') && $request->start_date) {
            $query->where('record_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('record_at', '<=', $request->end_date);
        }

        $weights = $query->orderBy('record_at', 'DESC')
            ->paginate(15);

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
        $weight = new Weight;
        $weight->record_at = $request->input('record_at');
        $weight->weight = $request->input('weight');
        $weight->user = $request->input('user');
        $weight->note = $request->input('note'); // 添加備註欄位
        $weight->save();

        return redirect('/dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function show(Weight $weight)
    {
        $user_id = Auth::user()->id;
        $weights = Weight::where('user', $user_id)
            ->orderBy('record_at', 'ASC')
            ->get();
        return view('chart', ['weights' => $weights]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // dd($request);
        $weight = Weight::find($id);
        $weight->record_at = $request->input('record_at');
        $weight->weight = $request->input('weight');
        $weight->user = $request->input('user');
        $weight->note = $request->input('note'); // 添加備註欄位
        $weight->save();
        return redirect('/record');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Weight $weight)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Weight  $weight
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        Weight::destroy($id);
        return redirect('/record');
    }
}
