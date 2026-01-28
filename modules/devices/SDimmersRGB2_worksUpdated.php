<?php
/**
 * 
 * Обрабатывает изменение свойств устройства, связанных с цветом и яркостью.
 *
 * Функция выполняет следующие задачи:
 *
 *  1. Обрабатывает изменения свойства "levelWork":
 *      - Получает новое значение levelMin - levelMax.
 *      - Конвертирует его в % 1-100.
 *      - Устанавливает уровень яркости (level)
 * 
 *  2. Обрабатывает изменения свойства "colorWork":
 *      - Получает новое значение {"x":<value>,"y":<value>}.
 *      - Конвертирует его в RGB Hex.
 *      - Устанавливает цвет (color)
 *
 * Входные параметры:
 * -------------------
 * @param array $params Ассоциативный массив, содержащий:
 *      - 'NEW_VALUE'   (mixed)  Новое значение изменённого свойства.
 *      - 'SOURCE'      (string) Источник изменения свойства.
 *      - 'PROPERTY'    (string) Имя свойства, которое изменилось.
 *
 * Важные свойства объекта:
 * ------------------------
 * - color             — текущий HEX-цвет устройства.
 * - colorSaved        — последний сохранённый HEX-цвет.
 * - level             — уровень яркости (1–100).
 * - levelSaved        — последний сохранённый уровень яркости.
 *
 * Используемые функции:
 * ----------------------
 * - normalizeRange($val, $min, $max, $type) — нормализует числовое значение.
 *
 * Примечания:
 * -----------
 * - Обработка не выполняется, если SOURCE == 'propertysUpdated'
 *   (во избежание рекурсии).
 *
 * @return void
 */
//

// --- Дефолтные свойства
$this->callMethod('byDefault');

$property = $params['PROPERTY'] ?? null;
$source   = strtok($params['SOURCE'] ?? '', ' ');
$levelMin = $this->getProperty('levelMin') ?? 1;
$levelMax = $this->getProperty('levelMax') ?? 254;

// Для цвета оставляем сырой JSON, для чисел ограничиваем нашими рамками
$value = ($property === 'colorWork')
    ? $params['NEW_VALUE']
    : normalizeRange($params['NEW_VALUE'], $levelMin, $levelMax, 'number');

// Защита от рекурсий и невалидных данных
if ($source === 'propertysUpdated' || is_null($value)) return;

//$this->setProperty('flag', 1);

// --- БЛОК ЦВЕТА (Конвертация XY в чистый HEX) ---
if ($property === 'colorWork') {
    $data = is_array($value) ? $value : json_decode($value, true);
    if (!$data) return;

    // Считываем координаты напрямую (как вы подтвердили)
    $x = isset($data['x']) ? (float)$data['x'] : 0.3127;
    $y = isset($data['y']) ? (float)$data['y'] : 0.3290;

    // Расчет XYZ (Y_ref = 1.0 дает максимальную яркость цвета)
    $Y_ref = 1.0; 
    $safeY = ($y < 0.000001) ? 0.000001 : $y;
    $X = ($Y_ref / $safeY) * $x;
    $Z = ($Y_ref / $safeY) * (1.0 - $x - $y);

    // Перевод в линейный RGB (Матрица sRGB D65)
    $r = $X * 3.2406 - $Y_ref * 1.5372 - $Z * 0.4986;
    $g = -$X * 0.9689 + $Y_ref * 1.8758 + $Z * 0.0415;
    $b = $X * 0.0557 - $Y_ref * 0.2040 + $Z * 1.0570;

    // Убираем отрицательные значения и нормализуем яркость каналов
    $r = max(0, $r); $g = max(0, $g); $b = max(0, $b);
    $maxChannel = max($r, $g, $b);
    if ($maxChannel > 0) {
        $r /= $maxChannel; $g /= $maxChannel; $b /= $maxChannel;
    }

    // Гамма-коррекция и перевод в 0-255
    $rgb = [];
    foreach ([$r, $g, $b] as $v) {
        $v = ($v <= 0.0031308) ? 12.92 * $v : 1.055 * pow($v, 1.0 / 2.4) - 0.055;
        $rgb[] = (int)round($v * 255);
    }

    $hex = sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
    
    // Записываем цвет (чистый оттенок без учета яркости лампы)
    $this->setProperty('color', $hex, 'worksUpdated');
    //$this->setProperty('colorSaved', $hex);
    return;
}

// --- БЛОК ЯРКОСТИ (Конвертация рабочего значения в проценты 1-100) ---
if ($property === 'levelWork') {
    // Рассчитываем процентное положение в диапазоне levelMin...levelMax
    if ($levelMax != $levelMin) {
        $level = ($value - $levelMin) / ($levelMax - $levelMin) * 100;
    } else {
        // Если границы не заданы или равны, берем последнее сохраненное значение
        $level = $this->getProperty('levelSaved') ?? 254;
    }

    // Ограничиваем результат (минимум 1%, чтобы не путать с выключенным состоянием)
    $level = (int)round(max(1, min(100, $level)));
    
    // Обновляем текущее состояние и сохраняем его в память
    $this->setProperty('level', $level, 'worksUpdated');
    //$this->setProperty('levelSaved', $level);
    
    return;
}