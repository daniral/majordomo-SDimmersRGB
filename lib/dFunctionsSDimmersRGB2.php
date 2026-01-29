<?php
/** Краткое описание всех функций
 *
 * normalizeRange($val, $min, $max, $type) — Проверяет и нормализует значение (число или HEX) в заданный диапазон.
 * adjustProperty($obj, $property, $value, $direction, $defaultStep, $min, $max)
 * — Универсальное изменение свойства (увеличить/уменьшить).
 * 
 *--------------------------------------------------------------------------------------------
 *| Функция             | Назначение                                                         |
 *| ------------------- | -------------------------------------------------------------------|
 *| `normalizeRange`    | Нормализует HEX или число в диапазон                               |
 *| `adjustProperty`    | Универсальное изменение свойства лампы                             |
 *--------------------------------------------------------------------------------------------
 */
//


/** Проверяет и нормализует значение: числовое или HEX (цвет/яркость).
* 
* Функция поддерживает:
* * Числовые значения в диапазоне $min..$max
* * HEX цвета (#RGB, #RRGGBB)
* * 12-значные HEX (например MAC-like)
*
* @param mixed  $val  Входное значение (число или HEX)
* @param int    $min  Минимальное значение диапазона для чисел
* @param int    $max  Максимальное значение диапазона для чисел
* @param string $type Тип значения: 'auto' (определяется автоматически), 'number' (число), 'color' (HEX цвет)
* @return int|string|null Возвращает:
* 
* нормализованное число в диапазоне $min..$max,
* HEX цвет в формате #RRGGBB,
* 12-значный HEX как есть,
* или null, если значение невалидно
*/
if (!function_exists('normalizeRange')) {
	function normalizeRange($val, $min = 0, $max = 100, $type = 'auto') {
		$val = strtolower(trim($val));
		if ($type === 'number') {
			// числовое значение
			if (is_numeric($val)) {
				return (int)max($min, min($max, $val));
			}
			return null; // не число
		}
		if ($type === 'color' || $type === 'auto') {
			// 12-значный HEX (например MAC-like)
			if (preg_match('/^[0-9a-f]{12}$/i', $val)) {
				return $val;
			}
			// Убираем # для проверки HEX
			$hex = ltrim($val, '#');
			// Длинный HEX #RRGGBB
			if (preg_match('/^[0-9a-f]{6}$/i', $hex)) {
				return '#' . $hex;
			}
			// Короткий HEX #RGB — разворачиваем в длинный #RRGGBB
			if (preg_match('/^[0-9a-f]{3}$/i', $hex)) {
				return '#' . $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			}
		}
		return null; // всё остальное — невалидно
	}
}

/** Универсальное изменение свойств (яркость, температура и т.п.)
 * adjustProperty($obj, $property, $value ?? null, $direction, $defaultStep, $min, $max);
 * @param object $obj — объект (обычно $this)
 * @param string $property — имя свойства ('level', 'cct' и т.п.)
 * @param mixed $value — шаг изменения (число или null)
 * @param string $direction — 'up' или 'down'
 * @param int $defaultStep — шаг по умолчанию (если не задан) = 10
 * @param int $min — минимальное значение (если не задан) = 0
 * @param int $max — максимальное значение (если не задан) = 100
 */
if (!function_exists('adjustProperty')) {
	function adjustProperty($obj, $property, $value = null, $direction = 'up', $defaultStep = 10, $min = 0, $max = 100)
    {
        $current = (int)$obj->getProperty($property);

        // Определяем шаг изменения
        $step = is_numeric($value) ? (int)$value : $defaultStep;
        $step = max(1, min($max, abs($step)));

        // Изменяем значение в нужную сторону
        $newValue = ($direction === 'up')
            ? min($max, $current + $step)
            : max($min, $current - $step);

        // Применяем новое значение
        $obj->callMethod("set" . ucfirst($property), ['value' => $newValue]);
    }
}