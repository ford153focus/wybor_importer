<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 20.07.18
 * Time: 15:06
 */

namespace Lum\Wybor;

require 'Utils.php';
require 'Config.php';

class CurrentSiteExport
{
    public $items = [];
    public $csvArray = [];
    public $header = [];

    function __construct()
    {
        $this->csvArray = Utils::csvFileToArray(Config::EXPORT_CSV_FILENAME);
        $this->cutHeader();
//        for ($i=0; $i<46; $i++) {echo "row[]= item['']; // {$this->header[0][$i]} / {$this->header[1][$i]} / {$this->header[2][$i]} \n";}
        $this->parseCsvArray();
    }

    public function cutHeader ()
    {
        $this->header[] = array_shift($this->csvArray);
        $this->header[] = array_shift($this->csvArray);
        $this->header[] = array_shift($this->csvArray);
    }

    public function parseCsvArray () {
        foreach ($this->csvArray as $row) {
            $hash=[];
            for ($i=0; $i<sizeof($this->header[0]); $i++) {
                @$hash[$this->header[0][$i]] = $row[$i];
            }
            $this->items[] = $hash;
        }

        return $this->items;
    }
}