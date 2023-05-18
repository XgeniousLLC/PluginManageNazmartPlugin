<?php

namespace Modules\PluginManage\Http\Helpers;

use Illuminate\Support\Facades\Cache;

class PluginJsonFileHelper
{
    //todo only work with single json file
    protected array|string|object $fileContents;
    protected string $pluginDirName;
    protected object $moduleList;
    public function __construct($pluginDirName)
    {
        $this->pluginDirName = $pluginDirName;
        $this->setModuleLists();
        if ($this->checkModuleFileExists()){
            $this->setFileContent();
        }
    }

    public function metaInfo(){
        $data = $this->getFileContent();

        $metaObject  = new \stdClass;
        $metaObject->name = $this->deliciousCamelcase($data->name);
        $metaObject->alias = $data->alias;
        $metaObject->description = $data->description;
        $metaObject->version = property_exists($data,"version") ? $data->version : "1.0.0";
        $metaObject->category = property_exists($data,"nazmartMetaData") ? __("External Plugin") : __("Core Plugin");
        $metaObject->status = $this->isPluginActive(); //check status using a private method;
        return $metaObject;
    }

    public function isPluginActive(){
        $moduleList = json_decode(file_get_contents(base_path("modules_statuses.json")),true);
        return array_key_exists($this->pluginDirName,$moduleList) && $moduleList["$this->pluginDirName"];
    }
    public function deliciousCamelcase($str)
    {
        $formattedStr = '';
        $re = '/
          (?<=[a-z])
          (?=[A-Z])
        | (?<=[A-Z])
          (?=[A-Z][a-z])
        /x';
        $a = preg_split($re, $str);
        $formattedStr = implode(' ', $a);
        return $formattedStr;
    }
    public function overrideData(array $data){
        $existingData  = $this->fileContents;
        foreach($data as $col => $value){
            if (property_exists($existingData,$col)){
                $existingData->$col = $value;
            }
        }
        $this->fileContents = $existingData;
        return $this;
    }
    public function saveFile(){
        file_put_contents($this->getModuleMetaFilePath(),$this->fileContents);
    }
    private function getJsonData(){

    }
    private function decodeData(){
        return json_decode($this->fileContents);
    }
    private function encodeData(){
        return json_encode($this->fileContents);
    }
    private function pluginName(){
        return $this->pluginDirName;
    }
    private function setFileContent(){
        return $this->fileContents = json_decode(file_get_contents($this->getModuleMetaFilePath()));
    }

    private function getFileContent(){
        return $this->fileContents;
    }

    private function getModuleMetaFilePath()
    {
        return module_path(implode("",explode(" ",$this->pluginDirName)))."/module.json";
    }

    private function checkModuleFileExists()
    {
        return file_exists($this->getModuleMetaFilePath()) && !is_dir($this->getModuleMetaFilePath());
    }
    public function saveModuleListFile(){
        file_put_contents(base_path("modules_statuses.json"),json_encode($this->moduleList));
    }
    private function setModuleLists(){
        $this->moduleList = Cache::remember("allModuleStatus",3600,function (){
            return json_decode(file_get_contents(base_path("modules_statuses.json")));
        });

        return $this;
    }
    public function changePluginStatus($status){
        Cache::forget("allModuleStatus");
        $pluginName = $this->pluginDirName;
//        $moduleList = $this->moduleList;
//        if (property_exists($moduleList,$pluginName)){
            $this->moduleList->$pluginName = $status;
//        }
        return $this;
    }
    public function removePlugin(){
        Cache::forever("allModuleStatus");
        $pluginName = $this->pluginDirName;
        $moduleList = $this->moduleList;
        if (property_exists($moduleList,$pluginName)){
            unset($this->moduleList->$pluginName);
        }
        return $this;
    }
}
