<?php
/** Обработчик изменения свойств RGB-ленты (presence, color, level, sceneName, scenesList).
 * 
 * Метод выполняет комплексную обработку входящих свойств устройства
 * и отвечает за:
 *
 * --- Цвет и яркость ---
 *  • Преобразование входящего цвета: HEX или предустановки (red, blue, lime и т.д.).
 *  • Нормализацию значений цвета и яркости.
 *  • Формирование строки HSV-HEX (rgbToHSVhex) и запись в colorWork.
 *  • Автоматическое включение устройства при изменении color или level.
 *  • Сохранение последних значений colorSaved и levelSaved.
 *
 * --- Защита от рекурсий ---
 *  • SOURCE="worksUpdated" — предотвращает циклические обновления.
 *  • SOURCE="autoMode" — отключает запись в flag и сохранение значений.
 *
 * --- Используемые свойства объекта ---
 *  • presence         — флаг присутствия (0/1)
 *  • status           — включено/выключено (0/1)
 *  • level            — текущая яркость (1–100)
 *  • color            — текущий HEX-цвет
 *  • levelSaved       — сохранённая яркость
 *  • colorSaved       — сохранённый цвет
 *  • colorWork        — строка для устройства
 *  • timerOff         — таймер авто-выключения
 *  • flag             — флаг изменения извне
 *
 * --- Параметры входящего события ---
 * @param array $params Ассоциативный массив:
 *      - string $params['PROPERTY']   Имя изменяемого свойства.
 *      - mixed  $params['NEW_VALUE']  Новое значение свойства.
 *      - string $params['SOURCE']     Источник события (защита от рекурсий).
 *
 * Логика обработки:
 *  1. byDefault() — установка дефолтов перед обработкой.
 *  2. Если SOURCE="worksUpdated" → выход (защита от рекурсий).
 *  3. Преобразование предустановок цвета (red, blue, lime ...).
 *  4. Нормализация числовых значений.
 *  5. Для color/level:
 *        - включение устройства,
 *        - установка work_mode=colour,
 *        - генерация colorWork,
 *        - сохранение *_Saved.
 *  6. Если источник не autoMode:
 *        - запись flag=1,
 *        - сохранение colorSaved / levelSaved / sceneNameSaved.
 *
 * @return void
 */


// --- Дефолтные свойства
$this->callMethod('byDefault');

$value = $params['NEW_VALUE'] ?? null;

// --- Преобразование предустановок цвета
static $transform = [
    'red' => '#ff0000', 'green' => '#00ff00', 'blue' => '#0000ff',
    'white' => '#ffffff', 'yellow' => '#ffff00', 'cyan' => '#00ffff',
    'magenta' => '#ff00ff', 'orange' => '#ffa500', 'purple' => '#800080',
    'pink' => '#ffc0cb', 'lime' => '#00ff00'
];
if (isset($transform[$value])) {
    $value = $transform[$value];
}

$property = $params['PROPERTY'] ?? null;
$source   = strtok($params['SOURCE'] ?? '', ' ');
$value = ($property === 'color')
    ? normalizeRange($value) // Если color
    : (($property === 'presence')
            ? normalizeRange($value, 0, 1, 'number') // Если presence (0 или 1)
            : normalizeRange($value, 1, 100, 'number')); // Иначе (level)

// --- Защита от рекурсий и не верных данных
if ($source === 'worksUpdated' || is_null($value)) {
    if(is_null($value) && $property != 'presence'){
        $this->setProperty($property, $this->getProperty($property . 'Saved'), 'worksUpdated');
    }
    return;
}

// --- Обработка presence
if ($property === 'presence') {
    if ((int)$this->getProperty('timerOff') > 0) {
        autoOff($this);
    }
    return;
}

// --- Обработка Цвет и Яркость (Управление ИЗ интерфейса НА устройство) ---
if ($property === 'color' || $property === 'level') {
    if (!$this->getProperty('status')) $this->setProperty('status', 1);

    if ($source !== 'autoMode') {
        $this->setProperty('flag', 1);
        $this->setProperty($property . 'Saved', $value);
    }

    // Если значение реально изменилось в интерфейсе — сохраняем локально
    if ($value != $this->getProperty($property)) {
        $this->setProperty($property, $value, 'worksUpdated');
    }

    if ($property === 'level') {
        // 1. Считываем границы устройства
        $levelMin = $this->getProperty('levelMin') ?? 1;
        $levelMax = $this->getProperty('levelMax') ?? 254;

        // 2. Пересчитываем 1-100% в рабочий диапазон лампы (например, 1-254)
        $workValue = (int)round($minLevel + ($maxLevel - $minLevel) * $value / 100);
        $workValue = max($minLevel, min($maxLevel, $workValue));
    } 
    
    if ($property === 'color') {
        // 1. HEX -> RGB
        $hex = ltrim($value, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // 2. Gamma correction
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // 3. Wide Gamut RGB -> XYZ -> XY
        $X = $r * 0.664511 + $g * 0.154324 + $b * 0.162028;
        $Y = $r * 0.283881 + $g * 0.668433 + $b * 0.047685;
        $Z = $r * 0.000088 + $g * 0.072310 + $b * 0.986039;

        if (($X + $Y + $Z) == 0) {
            $x = 0.3127; $y = 0.3290;
        } else {
            $x = $X / ($X + $Y + $Z);
            $y = $Y / ($X + $Y + $Z);
        }

        // 4. Формируем JSON для отправки на устройство
        $workValue = json_encode([
            'x' => (float)round($x, 4),
            'y' => (float)round($y, 4)
        ]);
    }

    // Отправляем конечное "рабочее" значение на устройство
    $this->setProperty($property . 'Work', $workValue, 'propertysUpdated');
    return;
}