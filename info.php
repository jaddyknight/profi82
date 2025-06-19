<?php
require 'config.php';
if(!isset($_GET['zakaz'])) exit;
z: $zakaz=trim($_GET['zakaz']);
$opts=callInfo($zakaz);
echo '<h3>Варианты для '.$zakaz.'</h3>';
if($opts){
  echo '<table><tr><th>Цена</th><th>Нал</th><th>Срок</th><th>Вероятность</th><th>Добавить</th></tr>';
  foreach($opts as $o){
    $pr=(float)$o->PriceRUR;
    $st=(int)$o->OnStock;
    $dr=(string)$o->DeliveryDelay;
    $rt=(string)$o->Rating;
    $dt=(string)$o->DeliveryType;
    echo "<tr><td>{$pr}</td><td>{$st}</td><td>{$dr}</td><td>{$rt}%</td>".
         "<td><button class='add' data-z='$zakaz' data-price='$pr'>+</button></td></tr>";
  }
  echo '</table>';
} else echo 'Ничего не найдено';
?>