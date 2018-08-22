<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 18.07.18
 * Time: 12:15
 */
require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/exceptions_error_handler.php';

require 'lib/CurrentSiteExport.php';
require 'lib/IncomingCsv.php';
require 'lib/OldSiteExport.php';
require 'lib/Vendors.php';

use Lum\Wybor;

$currentSiteExport = new Wybor\CurrentSiteExport();
$oldSiteExport = new Wybor\OldSiteExport();
$incoming = new Wybor\Incoming();

$incoming->fillDestinations();
$incoming->mergeSimilar();

$vendors = new Wybor\Vendors($currentSiteExport->items);

foreach ($incoming->items as &$item) {
    if ($item['Бренд'] !== '' && $item['Серия'] !== '') {
        $item['parent-id'] = $vendors->getItemParentId($item['Бренд'], $item['Серия']);
    }
}

$incoming->grabInfoFromOldExport($oldSiteExport);
$incoming->grabInfoFromOldSite();

//$incoming->grabFiles();

//setDefaultImages
foreach ($incoming->items as &$item) {
    $item['Изображение'] = $item['Изображение'] === '' ? sprintf("/images/cms/data/%s_logo.jpg",
        strtolower($item['Бренд'])) : $item['Изображение'];
}

$newExportArray = array_merge($currentSiteExport->header, $incoming->toArray());

Wybor\Utils::dumpArrayToCsvFile($newExportArray, __DIR__ . '/files/new.csv');
copy(__DIR__ . '/files/new.csv', Wybor\Config::DESTINATION_PATH . '/1801.csv');

true;
