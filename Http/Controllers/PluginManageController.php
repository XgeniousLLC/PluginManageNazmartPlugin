<?php

namespace Modules\PluginManage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Modules\PluginManage\Http\Helpers\PluginManageHelpers;

class PluginManageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        //todo write a call to access all json content and update it
        //todo return list of plugins devided by core and external
        $pluginList = PluginManageHelpers::getPluginLists();
        return view('pluginmanage::plugin-manage.index',compact("pluginList"));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('pluginmanage::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('pluginmanage::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('pluginmanage::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function delete_plugin(Request $request)
    {
        $request->validate([
           "plugin" => "required|string"
        ]);
        //todo:: remove this name from modules_status.json file
        File::deleteDirectory(module_path(implode("",explode(" ",$request->plugin))));
        return response()->json("ok");
    }
    public function change_status(Request $request)
    {
        $request->validate([
           "plugin" => "required|string",
           "status" => "required|string",
        ]);
        $status = $request->status == 1 ? false : true;
        PluginManageHelpers::getPluginInfo(implode("",explode(" ",$request->plugin)))->changePluginStatus($status )->saveModuleListFile();
//        dd($info);
//        File::deleteDirectory(module_path(implode("",explode(" ",$request->plugin))));
        return response()->json("ok");
    }
}
