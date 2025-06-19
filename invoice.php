<?php
require 'config.php';
?>
<!DOCTYPE html><html lang='ru'><head><meta charset='utf-8'><title>Накладная</title></head><body>
<h2>Накладная</h2>
<table><tr><th>Код</th><th>Наименование</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr>
<?php foreach($_SESSION['cart'] as $i): ?>
<tr><td><?=$i['zakaz']?></td><td><?=$i['name']?></td><td><?=$i['qty']?></td><td><?=$i['price']?></td><td><?=$i['qty']*$i['price']?></td></tr>
<?php endforeach; ?>
<tr><td colspan='4'>Итого</td><td><?=cartTotal()?></td></tr>
</table>
<button onclick='window.print()'>Печать</button>
</body></html>