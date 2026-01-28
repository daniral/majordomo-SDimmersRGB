<?php
/**
 * Class SDimmersRGB2
 *
 * Класс устройств RGB для MajorDoMo.
 * Наследуется от SControllers. Описывает свойства яркости, цвета,
 * рабочих параметров, а также методы управления устройством.
 *
 * ===========================================================
 * PROPERTIES:
 * ===========================================================
 *
 * @property string $color           Текущий цвет ленты (HEX 6 символов). 
 *                                   Формат: #RRGGBB или RRGGBB. DataKey. OnChange: propertysUpdated.
 *
 * @property string $colorWork       Рабочий цвет в формате {"hex":"#' . $color . '"}.
 *
 * @property string $colorSaved      Последний установленный цвет (HEX 6 символов).
 *
 * @property int    $level           Текущая яркость (1–100). 
 *                                   DataKey. OnChange: propertysUpdated.
 * 
 * ===========================================================
 * METHODS:
 * ===========================================================
 *
 * @method void setLevel(int $value)
 *      Установить уровень яркости (0–100).  
 *      Вызывается через MajorDoMo: 
 *      `callMethod('Объект.setLevel', array("value" => 0–100))`
 *
 * @method void setColor(string $value)
 *      Установить цвет в HEX формате (#RRGGBB или RRGGBB).  
 *      Вызывается через MajorDoMo: 
 *      `callMethod('Объект.setColor', array("value" => "#RRGGBB"))`
 *
 * @method void levelUp(int $value = 10)
 *      Увеличить яркость на указанное значение.  
 *      Если параметр $value не передан, используется значение по умолчанию 10.  
 *      Вызывается через MajorDoMo: 
 *      `callMethod('Объект.levelUp', array("value" => 1–100))` или просто 
 *      `callMethod('Объект.levelUp')` для +10.
 *
 * @method void levelDown(int $value = 10)
 *      Уменьшить яркость на указанное значение.  
 *      Если параметр $value не передан, используется значение по умолчанию 10.  
 *      Вызывается через MajorDoMo: 
 *      `callMethod('Объект.levelDown', array("value" => 1–100))` или просто 
 *      `callMethod('Объект.levelDown')` для -10.
 *
 * @method void propertysUpdated()
 *      Вызывается при изменении яркости, цвета или сцены.
 *
 * @method void worksUpdated()
 *      Вызывается при изменении рабочих параметров (colorWork / sceneWork).
 */

if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/SDimmersRGB2_' . SETTINGS_SITE_LANGUAGE . '.php')) {
	include_once(ROOT . 'languages/SDimmersRGB2_' . SETTINGS_SITE_LANGUAGE . '.php');
} else {
	include_once(ROOT . 'languages/SDimmersRGB2_default.php'); //
}

$this->device_types['dimmerRGB2'] = array(
	'TITLE' => 'Освещение(Dimmer RGB) - 2',
	'PARENT_CLASS' => 'SControllers',
	'CLASS' => 'SDimmersRGB2',
	'DESCRIPTION'=>'Dimmer RGB - 2',
	'PROPERTIES' => array(
		'color' => array('DESCRIPTION' => 'Цвет (RGB).', 'ONCHANGE' => 'propertysUpdated', 'DATA_KEY' => 1),
		'colorWork' => array('DESCRIPTION' => 'Рабочий цвет.', 'ONCHANGE' => 'worksUpdated'),
		'colorSaved' => array('DESCRIPTION' => 'Последний цвет.'),

		'level' => array('DESCRIPTION' => 'Яркость (1<-->100).', 'ONCHANGE' => 'propertysUpdated', 'DATA_KEY' => 1),
		'levelWork' => array('DESCRIPTION' => 'Рабочая яркость.', 'ONCHANGE' => 'worksUpdated'),
		'levelSaved' => array('DESCRIPTION' => 'Последняя яркость.', 'DATA_KEY' => 1),
		'levelMin' => array('DESCRIPTION' => 'Минимальная рабочая яркость', '_CONFIG_TYPE' => 'num'),
		'levelMax' => array('DESCRIPTION' => 'Максимальная рабочая яркость', '_CONFIG_TYPE' => 'num'),

		'dayColor' => array('DESCRIPTION' => 'Цвет днем', '_CONFIG_TYPE' => 'num',),
		'dayLevel' => array('DESCRIPTION' => 'Уровень яркости днем', '_CONFIG_TYPE' => 'num',),

		'nightColor' => array('DESCRIPTION' => 'Цвет ночью', '_CONFIG_TYPE' => 'num',),
		'nightLevel' => array('DESCRIPTION' => 'Уровень яркости ночью', '_CONFIG_TYPE' => 'num',),

		'autoOnOff' => array('DESCRIPTION' => 'Автовключение','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'1=Включено,0=Отключено'),
		'timerOff' => array('DESCRIPTION' => 'Выключить через(сек). 0-не выключать', '_CONFIG_TYPE' => 'num'),
		'workingDay' => array('DESCRIPTION' => 'Включать','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'1=День,2=Ночь,3=24 часа'),
		'workingBy' => array('DESCRIPTION' => 'Работать по','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'1=Время,2=Солнце,3=Датчик'),
		'dayBegin' => array('DESCRIPTION' => 'Начало режима день(hh:mm)', '_CONFIG_TYPE' => 'num'),
		'nightBegin' => array('DESCRIPTION' => 'Начало режима ночь(hh:mm)', '_CONFIG_TYPE' => 'num'),
		'sunriseTime' => array('DESCRIPTION' => 'Время восхода солнца'),
		'sunsetTime' => array('DESCRIPTION' => 'Время захода солнца'),
		'signSunrise' => array('DESCRIPTION' => 'Восход','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'1=прибавить,0=отнять'),
		'addTimeSunrise' => array('DESCRIPTION' => 'Часов:Минут(00:00)', '_CONFIG_TYPE' => 'num'),
		'signSunset' => array('DESCRIPTION' => 'Закат','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'1=прибавить,0=отнять'),
		'addTimeSunset' => array('DESCRIPTION' => 'Часов:Минут(00:00)', '_CONFIG_TYPE' => 'num'),
		'illuminanceMax' => array('DESCRIPTION' => 'Макc.освещение(датчик)', '_CONFIG_TYPE' => 'num'),
		'illuminanceFlag' => array('DESCRIPTION' => 'Стопер датчика освещения'),
		'illuminance' => array('DESCRIPTION' => 'Данные с датчика освещения', 'DATA_KEY' => 1),
		'presence' => array('DESCRIPTION' => 'Данные с датчика присутствия', 'ONCHANGE' => 'propertysUpdated', 'DATA_KEY' => 1),
		'flag' => array('DESCRIPTION' => 'Стопер запуска авто мода'),
	),
	'METHODS' => array(
		'turnOn' => array('DESCRIPTION' => 'Включить', '_CONFIG_SHOW' => 1),
		'turnOff' => array('DESCRIPTION' => 'Выключить', '_CONFIG_SHOW' => 1),
		'switch' => array('DESCRIPTION' => 'Переключить'),

		'levelUp' => array('DESCRIPTION' => 'Увеличить яркость.', '_CONFIG_SHOW' => 1, '_CONFIG_REQ_VALUE' => 1),
		'levelDown' => array('DESCRIPTION' => 'Уменьшить яркость.', '_CONFIG_SHOW' => 1, '_CONFIG_REQ_VALUE' => 1),
		'setLevel' => array('DESCRIPTION' => 'Установить уровень яркости.', '_CONFIG_SHOW' => 1, '_CONFIG_REQ_VALUE' => 1),

		'setColor' => array('DESCRIPTION' => 'Установиьт цвет(HEX).', '_CONFIG_SHOW' => 1, '_CONFIG_REQ_VALUE' => 1),
		
		'worksUpdated' => array('DESCRIPTION' => 'Запускается при смене рабочих параметров'),
		'propertysUpdated' => array('DESCRIPTION' => 'Запускается при смене параметров'),

		'byDefault' => array('DESCRIPTION' => 'Установить свойства по умолчанию.'),
		'createCommandsMenu' => array('DESCRIPTION' => 'Создает меню управления.', '_CONFIG_SHOW' => 1),
		'deleteCommandsMenu' => array('DESCRIPTION' => 'Удаляет меню управления.', '_CONFIG_SHOW' => 1),	
	),
);
