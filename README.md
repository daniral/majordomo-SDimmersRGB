# 💡 Dimmer RGB - 2
## Простое устройство для MajorDoMo

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-blue" />
  <img src="https://img.shields.io/badge/MajorDoMo-Device%20Module-green" />
  <img src="https://img.shields.io/badge/Status-Production-success" />
  <img src="https://img.shields.io/badge/Type-Smart%20Lighting-yellow" />
  <img src="https://img.shields.io/badge/Version-2.0-orange" />
</p>
---

## 📘 Описание

**`SDimmersRGB`** — расширяет класс *SControllers* 
> Простое устройство диммируемого освещения для MajorDoMo.

---  
Поддерживает:

🎨 Цвет

💡 Яркость

🕒 Автовключение / автоотключение

👁 Работа по датчику движения / света / времени / солнцу

🌗 Авто-режимы День / Ночь / 24 часа

🧩 Создание/удаление меню управления MajorDoMo

При первом запуске автоматически создаются все необходимые свойства.

---

# ⚙️ Привязка свойств

| Tuya поле      | Свойство MajorDoMo |
| -------------- | ------------------ |
| `state`        | `status`           |
| `brightness`   | `levelWork`        | 
| `color`        | `colorWork`        | 
---

# 🚦 Обычный режим

Включение лампы:
```php
callMethod('ObjectName.turnOn');
```
Если параметры не указаны — берутся сохранённые (`...Saved`) или значения по умолчанию:

| Параметр          | Если пусто |
| ----------------- | ---------- |
| `levelSaved`      | 100        |
| `colorSaved`      | #FFFFFF  |

Включение с параметрами:
```php
callMethod('Object.turnOn', [
  'level'      => 1..100,
  'color'      => '#RRGGBB' , '#RGB' или присеты,
]);
```
При обычном включении ставится `flag=1`, блокируя авто-режим.

---

# 🤖 Авто-режим

Запуск без параметров:
```php
callMethod('Object.turnOn', ['autoMode' => 1]);
```
Устанавливаются значения которые указаны в свойствах дня и ночи.

Запуск с принудительными параметрами:
```php
callMethod('Object.turnOn', [
  'autoMode'   => 1,
  'level'      => 1..100,
  'color'      => '#RRGGBB' , '#RGB' или присеты,
]);
```
Эти значения устанавливаются один раз если вызвать в следующий раз без параметров то установятся те которые указаны в свойствах дня и ночи.
Можно не задавать все значения.
Те которые не заданы установятся из свойств дня и ночи

Особенности:

* Работает таймер `timerOff` — авто-выключение.
* Пока `presence=1` — не выключается.
* При изменении `presence` = 0 — запускается `autoOff()`.
* Три режима работы: **День**, **Ночь**, **24 часа**

# Режимы по источнику (`workingBy`)

| Значение | Описание             |
| -------- | -------------------- |
| `1`      | По времени           |
| `2`      | По солнцу            |
| `3`      | По датчику освещения |

### 🌅 По солнцу:

Требует свойства:

* `sunriseTime`
* `sunsetTime`

Можно корректировать:

* `addTimeSunrise` + `signSunrise`
* `addTimeSunset` + `signSunset`

### 💡 По датчику освещения:

Использует:

* свойство `illuminance`
* порог `illuminanceMax`

Не проверялось. Нету датчика.
---

# 🔧 Методы 

## ⛔ Отключение 
```php
callMethod('Object.turnOff');
```
Сбрасывает: `flag=0` `illuminanceFlag = 0`

---
## 🔁 Переключение
```php
callMethod('Object.switch');
```
Поведение:

* Если лампа **в авто-режиме** — включит сохранённые значения.
* Если **выключена** — включит сохранённые значения.
* Если **включена вручную** — выключит.
---
## 🎨 Управление цветом и яркостью

## Цвет может задаваться:

✔ HEX-кодами

* `#RRGGBB`
* `#RGB`

✔ Цветовыми пресетами

Используемые имена:
```
red, green, blue, white, yellow,
cyan, magenta, orange, purple,
pink, lime
```

| Метод            | Описание                 |
| ---------------- | ------------------------ |
| `setColor`       | Установить цвет          |
| `setLevel`       | Установить яркость цвета |
| `levelUp`        | Увеличить яркость        |
| `levelDown`      | Уменьшить яркость        |

```php
callMethod('Имя Объекта.setColor', array("value"=>`#RRGGBB` или `#RGB` или присет));
callMethod('Имя Объекта.setLevel', array("value"=>1--100));
callMethod('Имя Объекта.levelUp', array("value"=>1--100));
  *callMethod('Имя Объекта.levelUp'); увеличит на 10
callMethod('Имя Объекта.levelDown', array("value"=>1--100));
  *callMethod('Имя Объекта.levelUp'); уменьшит на 10
```
Все методы → `flag=1`.

---
## 🧩 Меню управления

Создать меню:
```php
callMethod('Object.createCommandsMenu');
```

Удалить меню:
```php
callMethod('Object.deleteCommandsMenu');
```

---

# 📝 Поведение при первом запуске

Метод `turnOn` автоматически создаёт все недостающие свойства устройства.
Заполняет дефолтными значениями.
---