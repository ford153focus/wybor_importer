<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 06.08.18
 * Time: 13:03
 */

namespace Lum\Wybor;


class Series
{
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

class Vendor
{
    public $id;
    public $name;
    public $series = [];

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

class Vendors
{
    public $vendors = [];

    public function __construct($items)
    {
        $this->collectVendors($items);
        $this->collectSeries($items);
    }

    private function collectVendors($items) {
        for ($i=0; $i<count($items); $i++) {
            if (($items[$i]['type-id'] === "80") && ($items[$i]['parent-id']==="2")) {
                $this->vendors[] = new Vendor($items[$i]['id'], $items[$i]['name']);
            }
        }
    }

    private function collectSeries($items) {
        foreach ($items as $datum) {
            foreach ($this->vendors as $vendor) {
                if ( $datum['type-id'] === "80" ) {
                    if ( $datum['parent-id'] === $vendor->id ) {
                        $vendor->series[] = new Series($datum['id'], $datum['name']);
                    }
                }
            }
        }
    }

    public function getItemParentId($vendor, $series) {
        foreach ($this->vendors as $vendor0) {
            foreach ($vendor0->series as $series0) {
                if ($vendor0->name === $vendor && $series0->name === $series) {
                        return $series0->id;
                }
            }
        }
        var_dump("Серия {$series} вендора {$vendor} отсутствует");
        return $vendor0->id;
    }
}
