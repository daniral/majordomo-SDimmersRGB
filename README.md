# 💡 Dimmer RGB
## Простое устройство для MajorDoMo

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-blue" />
  <img src="https://img.shields.io/badge/MajorDoMo-Device%20Module-green" />
  <img src="https://img.shields.io/badge/Status-Production-success" />
  <img src="https://img.shields.io/badge/Type-Smart%20Lighting-yellow" />
  <img src="https://img.shields.io/badge/Version-1.0-orange" />
</p>
---

## 📘 Описание

**`SDimmersRGB`** — расширяет класс *SControllers* 
> Простое устройство диммируемого освещения с цветом для MajorDoMo.

---  
Поддерживает:

🎨 Цвет  
  * Принимает данные от устройства  в формате json {"x":__,"y":__}(можно добавить другие)  конвертирует в RGB.  
  И на оборот.  
  
💡 Яркость  
  * Принимает данные в указаном диапазоне (levelMin - levelMax) конвертирует в проценты(1-100).  

---

# ⚙️ Привязка свойств

| Tuya поле      | Свойство MajorDoMo |
| -------------- | ------------------ |
| `state`        | `status`           |
| `brightness`   | `levelWork`        | 
| `color`        | `colorWork`        | 
---


# 🔧 Методы 

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
---