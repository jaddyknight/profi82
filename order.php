<?php
// order.php
session_start();
require 'config.php';

// Отладка
ini_set('display_errors',1);
error_reporting(E_ALL);

// Если корзина пуста и не параметр thank, редирект на поиск
if (empty($_SESSION['cart']) && !isset($_GET['thank'])) {
    header('Location: /search.php');
    exit;
}

/**
 * Отправляем позиции заказа в корзину поставщика через SOAP
 * @param array $items
 * @return array
 */
function sendOrderToSupplier(array $items): array {
    $url    = MIKADO_BASKET_URL;
    $action = 'http://mikado-parts.ru/ws1/Basket_Add';
    $client = MIKADO_CLIENT_ID;
    $pass   = MIKADO_PASSWORD;
    $results = [];

    foreach ($items as $item) {
        $zakaz = htmlspecialchars($item['zakaz'], ENT_QUOTES);
        $qty   = intval($item['qty']);

        // Собираем SOAP-запрос
        $xmlReq = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws1="http://mikado-parts.ru/ws1/">
  <soapenv:Body>
    <ws1:Basket_Add>
      <ZakazCode>{$zakaz}</ZakazCode>
      <QTY>{$qty}</QTY>
      <DeliveryType>0</DeliveryType>
      <Notes></Notes>
      <ClientID>{$client}</ClientID>
      <Password>{$pass}</Password>
      <ExpressID>0</ExpressID>
      <StockID>1</StockID>
    </ws1:Basket_Add>
  </soapenv:Body>
</soapenv:Envelope>
XML;

        // Выполняем запрос
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "' . $action . '"'
            ],
            CURLOPT_POSTFIELDS     => $xmlReq,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $resp = curl_exec($ch);
        if (curl_errno($ch)) {
            $results[] = ['zakaz'=>$item['zakaz'], 'status'=>'error', 'msg'=>curl_error($ch), 'id'=>''];
            curl_close($ch);
            continue;
        }
        curl_close($ch);

        // Парсим ответ
        libxml_use_internal_errors(true);
        $sxml = simplexml_load_string($resp);
        if (!$sxml) {
            $results[] = ['zakaz'=>$item['zakaz'], 'status'=>'fail', 'msg'=>'Invalid XML', 'id'=>''];
            continue;
        }
        // Регистрируем пространства имён для XPath
        $sxml->registerXPathNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $sxml->registerXPathNamespace('ws1', 'http://mikado-parts.ru/ws1/');
        $nodes = $sxml->xpath('//soapenv:Body/ws1:Basket_AddResponse/ws1:Basket_AddResult');
        if (empty($nodes)) {
            $results[] = ['zakaz'=>$item['zakaz'], 'status'=>'fail', 'msg'=>'No Result Node', 'id'=>''];
        } else {
            $node = $nodes[0];
            $msg  = (string)$node->Message;
            $id   = (string)$node->ID;
            $status = ($msg === 'OK' ? 'ok' : 'fail');
            $results[] = ['zakaz'=>$item['zakaz'], 'status'=>$status, 'msg'=>$msg, 'id'=>$id];
        }
    }
    return $results;
}

// Обработка отправки заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['last_order'] = [
        'client' => ['name'=>trim($_POST['client_name']),'phone'=>trim($_POST['client_phone'])],
        'items'  => $_SESSION['cart']
    ];
    $_SESSION['send_results'] = sendOrderToSupplier($_SESSION['cart']);
    clearCart();
    header('Location: /order.php?thank=1');
    exit;
}

// Страница благодарности
if (isset($_GET['thank'])) {
    $order   = $_SESSION['last_order'];
    $results = $_SESSION['send_results'];
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head><meta charset="utf-8"><title>Спасибо за заказ</title></head>
    <body>
      <h1>Спасибо за заказ!</h1>
      <p><strong>Имя:</strong> <?=htmlspecialchars($order['client']['name'],ENT_QUOTES)?></p>
      <p><strong>Телефон:</strong> <?=htmlspecialchars($order['client']['phone'],ENT_QUOTES)?></p>
      <h2>Статус отправки:</h2>
      <table border="1" cellpadding="5">
        <tr><th>Артикул</th><th>Статус</th><th>Сообщение</th><th>ID</th></tr>
        <?php foreach ($results as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['zakaz'],ENT_QUOTES)?></td>
            <td><?=htmlspecialchars($r['status'],ENT_QUOTES)?></td>
            <td><?=htmlspecialchars($r['msg'],ENT_QUOTES)?></td>
            <td><?=htmlspecialchars($r['id'],ENT_QUOTES)?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <p><a href="/search.php">Новый поиск</a></p>
    </body>
    </html>
    <?php
    exit;
}

// Форма оформления заказа
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Оформление заказа</title></head>
<body>
  <h1>Оформление заказа</h1>
  <form method="post" action="/order.php">
    <label>Имя: <input type="text" name="client_name" required></label><br>
    <label>Телефон: <input type="text" name="client_phone" required></label><br>
    <button type="submit">Подтвердить заказ</button>
  </form>
  <p><a href="/cart.php">Вернуться в корзину</a></p>
</body>
</html>
