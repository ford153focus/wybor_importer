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
    const DESTINATIONS = [
        'Системы телекоммуникации и связи',
        'Электростанции и подстанции или Электроэнергетическое оборудование (EPS)',
        'Источники бесперебойного питания (UPS)',
        'Системы аварийного освещения',
        'Системы пожарной и охранной сигнализации',
        'Резервное питание различных промышленных объектов',
        'Питание переносного оборудования',
        'Автоматика на железнодорожном транспорте или Железнодорожная автоматика',
        'Системы солнечной и ветроэнергетики',
        'Инвалидные коляски',
        'Гольф-кары',
        'Уборочная техника',
        'Электроинструмент',
        'Электронные устройства и оборудование',
        'Аккумуляторы для лодок и катеров',
        'Аккумуляторы для ЖД автоматики'
    ];
    public $items = [];

    public function __construct()
    {
        $this->parseFile();
        $this->cleanUp();
    }

    private function parseFile()
    {
        $csvArray = Utils::csvFileToArray(Config::INCOMING_CSV_FILENAME);

        for ($i = 3; $i < count($csvArray); $i++) {
            $item = [];

            $item['Бренд'] = $csvArray[$i][1];
            $item['Серия'] = $csvArray[$i][2];
            $item['Модель'] = $csvArray[$i][3];
            $item['Артикул'] = $csvArray[$i][4];
            $item['Напряжение'] = $csvArray[$i][5];
            $item['Номинальная Емкость'] = $csvArray[$i][6];
            $item['Мощность'] = $csvArray[$i][7];
            $item['Максимальный ток разряда'] = $csvArray[$i][8];
            $item['Ток короткого замыкания'] = $csvArray[$i][9];
            $item['Внутреннее сопротивление'] = $csvArray[$i][10];
            $item['Диапазон рабочих температур'] = $csvArray[$i][11];
            $item['Номинальная рабочая температура'] = $csvArray[$i][12];
            $item['Длина'] = $csvArray[$i][13];
            $item['Ширина'] = $csvArray[$i][14];
            $item['Высота'] = $csvArray[$i][15];
            $item['Высота  (с клеммами)'] = $csvArray[$i][16];
            $item['Вес'] = $csvArray[$i][17];
            $item['Выводы'] = $csvArray[$i][18];
            $item['Материал корпуса'] = $csvArray[$i][19];
            $item['Технология'] = $csvArray[$i][20];
            $item['Срок службы'] = $csvArray[$i][21];
            $item['Область применения'] = $csvArray[$i][22];
            $item['Пункты меню'] = $csvArray[$i][23];

            $item['Описание'] = '';
            $item['Изображение'] = '';
            $item['PDF-файл'] = '';
            $item['Текст'] = '';
            $item['Применение'] = '';

            $this->items[] = $item;
        }
    }

    private function cleanUp()
    {
        foreach ($this->items as &$item) {
            foreach ($item as &$cell) {
                $cell = trim($cell);
                if ($cell === "не применимо") {
                    $cell = "";
                }
                if ($cell === "нет данных") {
                    $cell = "";
                }
            }
        }
    }

    public function fillDestinations()
    {
        foreach ($this->items as &$item) {
            if ($item['Область применения'] !== '') {
                if ($item['Область применения'] === 'все' || $item['Область применения'] === 'всё') {
                    $item['Область применения'] = implode(', ', self::DESTINATIONS);
                } else {
                    $str = '';
                    foreach (explode(', ', $item['Область применения']) as $dest) {
                        $str .= self::DESTINATIONS[(int)$dest - 1] . ', ';
                    }
                    $str = rtrim($str, ', ');
                    $item['Область применения'] = $str;
                }
            }
        }
    }

    public function mergeSimilar()
    {
        for ($i = 0; $i < count($this->items); $i++) {
            for ($j = 0; $j < count($this->items); $j++) {
                if ($i !== $j) { // items is not the same (issue of double brute force)
                    $item1 = $this->items[$i];
                    $item2 = $this->items[$j];
                    if ($item1['Бренд'] === $item2['Бренд'] && $item1['Модель'] === $item2['Модель']) { // but same model
                        foreach ($item1 as $key => $value) { // iterate item props
                            if ($item1[$key] !== $item2[$key] && $item2[$key] !== '') { // if props value not same and empty - concatinate values
                                $item1[$key] = $item1[$key] === '' ? $item2[$key] : "{$item1[$key]}, $item2[$key]";
                            }
                        }
                        $this->items[$i] = $item1;
                        unset($this->items[$j]);
                        array_splice($this->items, 0, 0); //reindex
                        $this->mergeSimilar();
                        return;
                    }
                }
            }
        }
    }

    public function grabInfoFromOldExport(OldSiteExport $oldSiteExport) : void
    {
        foreach ($oldSiteExport->items as &$oldExportItem) {
            foreach ($this->items as &$item) {
                if ($item['Модель'] === str_replace(' ', '', $oldExportItem['name'])) {
                    if ($item['Бренд'] === $oldSiteExport->getVendorNameByItem($oldExportItem)) {
                        $item['Применение'] = $oldExportItem['primenenie'];
                        $item['Описание'] = $oldExportItem['descr'];
                        $item['Изображение'] = $oldExportItem['menu_pic_a'];
                        $item['PDF-файл'] = $oldExportItem['fajl_pdf'];
                        $item['Текст'] = $oldExportItem['tekst'];
                        if ($item['Пункты меню'] === '') {
                            $doc = new \DOMDocument('1.0', 'UTF-8');
                            $doc->loadHTML('<?xml encoding="UTF-8">' . $oldExportItem['primenenie']);
                            $sxml = simplexml_import_dom($doc);
                            foreach ($sxml->xpath('body/ul/li') as $li) {
                                $item['Пункты меню'] .= $li->__toString() . ',';
                            }
                            $item['Пункты меню'] = rtrim($item['Пункты меню'], ',');
                        }
                    }
                }
            }
        }
    }

    public function grabInfoFromOldSite() : void
    {
        foreach ($this->items as &$item) {
            if ($item['PDF-файл'] === '') {
                if (Utils::checkUrlForCode200("http://www.wybor-battery.com/doc/{$item['Бренд']}/{$item['Модель']}.pdf") === true) {
                    $item['PDF-файл'] = "/doc/{$item['Бренд']}/{$item['Модель']}.pdf";
                }
            }
        }
    }

    public function grabFiles() : void
    {
        foreach ($this->items as $item) {
            $this->grabFile($item['PDF-файл']);
            $this->grabFile($item['Изображение']);
        }
    }

    public static function grabFile($url) : void
    {
        if ($url === '') {
            return;
        }

        $url = 'http://www.wybor-battery.com/' . $url;

        $file = Config::DESTINATION_PATH . $url;

        $url = str_replace('//', '/', $url);
        $url = str_replace('//', '/', $url);

        $file = str_replace('//', '/', $file);
        $file = str_replace('//', '/', $file);

        $folder = dirname($file);

        print_r("Downloading $url \n");

        if (!file_exists($file)) {
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            file_put_contents($file, fopen($url, 'r'));
        }
    }

    function toArray(): array
    {
        $newExportArray = [];
        $id = 11000;
        foreach ($this->items as $item) {
            $row = [];

            $row[] = $id; // id / id / native
            $row[] = $item['Модель']; // name / Наименование / native
            $row[] = '82'; // type-id / Идентификатор типа / native
            $row[] = '1'; // is-active / Активность / native
            $row[] = '1'; // template-id / Идентификатор шаблона / native
            $row[] = $item['parent-id']; // parent-id / id родительской страницы / native
            $row[] = sprintf("Аккумулятор %s серии %s - купить в Санкт-Петербурге и Москве", $item['Бренд'],
                $item['Серия']); //title / Поле TITLE / string
            $row[] = sprintf("Аккумулятор %s серии %s", $item['Бренд'], $item['Серия']); //h1 / Поле H1 / string
            $row[] = sprintf("Аккумуляторная батарея %s %s от производителя по оптовым ценам. Звоните и заказывайте прямо сейчас!",
                $item['Бренд'], $item['Модель']); //meta_descriptions / Поле meta DESCRIPTIONS / string
            $row[] = $item['Текст']; // tekst / Описание серии / wysiwyg
            $row[] = ''; // index_source / Источник индекса / int
            $row[] = ''; // index_state / Индексация / float
            $row[] = ''; // index_date / Дата индексации / date
            $row[] = ''; // index_choose / Выбран для индексации / boolean
            $row[] = ''; // index_level / Установленный уровень вложенности / int
            $row[] = ''; // meta_keywords / Поле meta KEYWORDS / text
            $row[] = $item['Изображение']; // menu_pic_a / Изображение активного раздела / img_file
            $row[] = $item['Описание']; // descr / Описание / wysiwyg
            $row[] = ''; // logo_proizvoditelya / Лого производителя / img_file
            $row[] = ''; // vyvodit_na_glavnoj / Выводить на главной / boolean
            $row[] = ''; // izobrazhenie_v_slajder / Изображение в слайдер / img_file
            $row[] = $item['Применение']; // primenenie / Применение / wysiwyg
            $row[] = ''; // date_create_object / Дата создания объекта / date
            $row[] = '0'; // price / Цена / price
            $row[] = $item['Бренд']; // brend / Бренд / relation
            $row[] = $item['Серия']; // seriya / Серия / relation
            $row[] = $item['Артикул']; // artikul / Артикул / string
            $row[] = $item['Ток короткого замыкания']; // tok_korotkogo_zamykaniya / Ток короткого замыкания / float
            $row[] = $item['Внутреннее сопротивление']; // vnutrennee_soprotivlenie / Внутреннее сопротивление / float
            $row[] = $item['Диапазон рабочих температур']; // diapazon_rabochih_temperatur / Диапазон рабочих температур / string
            $row[] = $item['Номинальная рабочая температура']; // nominalnaya_rabochaya_temperatura / Номинальная рабочая температура / float
            $row[] = $item['Материал корпуса']; // material_korpusa / Материал корпуса / relation
            $row[] = $item['Пункты меню']; // oblast_primeneniya / Область применения / multiple-relation
            $row[] = $item['Напряжение']; // napryazhenie_v_unom / Напряжение / relation
            $row[] = $item['Технология']; // tehnologiya / Технология / relation
            $row[] = $item['Срок службы']; // srok_sluzhby / Срок службы / float
            $row[] = $item['Длина']; // dlina_mm / Длина / float
            $row[] = $item['Номинальная Емкость'] === '' ? '0' : $item['Номинальная Емкость'];; // emkost / Номинальная Емкость / float
            $row[] = $item['Ширина']; // shirina_mm / Ширина / float
            $row[] = $item['Высота']; // vysota_mm / Высота / float
            $row[] = $item['Вес']; // ves_kg / Вес / float
            $row[] = $item['Высота  (с клеммами)']; // vysota_s_klemmami_mm / Высота (с клеммами) / float
            $row[] = $item['Выводы']; // vyvody / Выводы / string
            $row[] = $item['Максимальный ток разряда']; // maksimalnyj_tok_razryada / Максимальный ток разряда / float
            $row[] = $item['Мощность']; // mownost / Мощность / float
            $row[] = $item['PDF-файл']; // fajl_pdf / Файл pdf / file

            $newExportArray[] = $row;
            $id++;
        }
        return $newExportArray;
    }
}