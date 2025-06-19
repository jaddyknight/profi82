<?php
require 'config.php';
if (empty($_SESSION['logged_in'])) header('Location:/');
$parts = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $code = trim($_POST['code']);
    $parts = callSearch($code);
}
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Поиск</title>
<style>body{font-family:sans-serif;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:4px;}button.var{cursor:pointer;}#themeToggle{position:fixed;top:10px;right:10px;}</style>
</head><body>
<button id="themeToggle">Тема</button>
<a href="/cart.php">Корзина (<?php echo count($_SESSION['cart']);?>)</a>
<form method="post" action="/search.php">
  <input name="code" placeholder="Артикул" required autocomplete="off">
  <button>Поиск</button>
</form>
<?php if($parts):?>
<table><tr><th>Код заказа</th><th>Наименование</th><th>Цена</th><th>Остатки</th><th>Действия</th></tr>
<?php foreach($parts as $item):
    $zakaz=(string)$item->ZakazCode;
    $name=(string)$item->Name;
    $price=(float)$item->PriceRUR;
    // остатки первые 2
    $stocks=[];
    if(isset($item->OnStocks->StockLine)) foreach($item->OnStocks->StockLine as $line) $stocks[]=(string)$line->StokName.': '.(string)$line->StockQTY;
    $stocks = array_slice($stocks,0,2);
?>
<tr data-zakaz="<?php echo $zakaz;?>">
<td><?php echo $zakaz;?></td>
<td><?php echo htmlspecialchars($name);?></td>
<td><?php echo $price;?></td>
<td><?php echo htmlspecialchars(implode('<br>',$stocks));?></td>
<td>
  <?php if((string)$item->CodeType==='OEM' || (string)$item->CodeType==='AnalogOEM'):?>
    <button class="var" data-zakaz="<?php echo $zakaz;?>">Варианты</button>
  <?php endif;?>
  <button class="add" data-zakaz="<?php echo $zakaz;?>" data-name="<?php echo htmlspecialchars($name);?>" data-price="<?php echo $price;?>">В корзину</button>
</td>
</tr>
<?php endforeach;?>
</table>
<div id="variants"></div>
<?php endif;?>
<script>
// theme toggle
let dark=false;
document.getElementById('themeToggle').onclick=()=>{
  document.body.style.background=dark?'':'#222';
  document.body.style.color=dark?'':'#ddd'; dark=!dark;
};
// autocomplete history
const hist = JSON.parse(localStorage.getItem('hist')||'[]');
const inp = document.querySelector('input[name=code]');
inp.addEventListener('input',()=>{/* skip for brevity */});
// AJAX variants
document.querySelectorAll('button.var').forEach(btn=>btn.onclick=e=>{
  const z=btn.dataset.zakaz;
  fetch('/info.php?zakaz='+z).then(r=>r.text()).then(html=>{
    document.getElementById('variants').innerHTML=html;
  });
});
// add to cart
document.querySelectorAll('button.add').forEach(btn=>btn.onclick=e=>{
  const z=btn.dataset.zakaz, n=btn.dataset.name, p=btn.dataset.price;
  fetch('/cart.php?action=add&zakaz='+z+'&name='+encodeURIComponent(n)+'&price='+p)
    .then(()=>location.reload());
});
</script>
</body></html>