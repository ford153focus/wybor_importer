<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 20.07.18
 * Time: 15:06
 */

namespace Lum\Wybor;


class Incoming
{
    public $items = [];

    public function __construct()
    {
        $this->parseFile();
        $this->cleanUp();
    }

    private function parseFile()
    {
        $simpleXMLElement = simplexml_load_file(__DIR__.'/../files/incoming.fods');
        $rows = $simpleXMLElement->xpath('/office:document/office:body/office:spreadsheet/table:table/table:table-row');

        for ($i=3; $i<count($rows); $i++) {
            $cells = $rows[$i]->xpath('table:table-cell');
            $item = [];
            $item['Бренд'] = $this->parseCell($cells[1]);
            $item['Серия'] = $this->parseCell($cells[2]);
            $item['Модель'] = $this->parseCell($cells[3]);
            $item['Артикул'] = $this->parseCell($cells[4]);
            $item['Напряжение'] = $this->parseCell($cells[5]);
            $item['Номинальная Емкость'] = $this->parseCell($cells[6]);
            $item['Мощность'] = $this->parseCell($cells[7]);
            $item['Максимальный ток разряда'] = $this->parseCell($cells[8]);
            $item['Ток короткого замыкания'] = $this->parseCell($cells[9]);
            $item['Внутреннее сопротивление'] = $this->parseCell($cells[10]);
            $item['Диапазон рабочих температур'] = $this->parseCell($cells[11]);
            $item['Номинальная рабочая температура'] = $this->parseCell($cells[12]);
            $item['Длина'] = $this->parseCell($cells[13]);
            $item['Ширина'] = $this->parseCell($cells[14]);
            $item['Высота'] = $this->parseCell($cells[15]);
            $item['Высота  (с клеммами)'] = $this->parseCell($cells[16]);
            $item['Вес'] = $this->parseCell($cells[17]);
            $item['Выводы'] = $this->parseCell($cells[18]);
            $item['Материал корпуса'] = $this->parseCell($cells[19]);
            $item['Технология'] = $this->parseCell($cells[20]);
            $item['Срок службы'] = $this->parseCell($cells[21]);
            $item['Область применения'] = $this->parseCell($cells[22]);
            $item['Пункты меню'] = $this->parseCell($cells[23]);

            $item['Описание'] = '';
            $item['Изображение'] = '';
            $item['PDF-файл'] = '';
            $item['Текст'] = '';

            $this->items[] = $item;
        }
    }

    private function parseCell(\SimpleXMLElement $node) {
        $node = $node->xpath('text:p');
        if ($node === []) {
            return '';
        } else {
            return $node[0]->__toString();
        }
    }

    private function cleanUp ()
    {
        foreach ($this->items as &$item) {
            foreach ($item as &$cell) {
                $cell = trim($cell);
                if ($cell === "не применимо") {$cell = "";}
                if ($cell === "нет данных") {$cell = "";}
            }
        }
    }
}