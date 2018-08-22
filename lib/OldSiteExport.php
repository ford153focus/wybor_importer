<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 20.07.18
 * Time: 21:03
 */

namespace Lum\Wybor;


class OldSiteExport
{
    public $items = [];
    public $csvArray = [];
    public $header = [];

    function __construct()
    {
        $this->csvArray = Utils::csvFileToArray(Config::OLD_SITE_EXPORT_CSV_FILENAME);
        $this->charsetFix();
        $this->cutHeader();
        $this->parseCsvArray();
    }

    private function charsetFix()
    {
        foreach ($this->csvArray as &$row) {
            foreach ($row as &$cell) {
                $cell = iconv('windows-1251', 'utf-8', $cell);
            }
        }
    }

    public function cutHeader()
    {
        $this->header[] = array_shift($this->csvArray);
        $this->header[] = array_shift($this->csvArray);
        $this->header[] = array_shift($this->csvArray);
    }

    public function parseCsvArray()
    {
        foreach ($this->csvArray as $row) {
            $hash = [];
            for ($i = 0; $i < sizeof($this->header[0]); $i++) {
                @$hash[$this->header[0][$i]] = $row[$i];
            }
            $this->items[] = $hash;
        }

        return $this->items;
    }

    public function getVendorNameByItem(array $requiredItem): string
    {
        foreach ($this->items as &$possibleSeries) {
            if ($possibleSeries['id'] == $requiredItem['parent-id']) {
                $requiredItemSeries = $possibleSeries;

                foreach ($this->items as &$possibleVendor) {
                    if ($possibleVendor['id'] == $requiredItemSeries['parent-id']) {
                        return $possibleVendor['name'];
                    }
                }
            }
        }

        return '';
    }
}