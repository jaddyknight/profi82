<?php
// config.php
session_start();

// Учетные данные API Mikado
// Ваши реальные данные
const MIKADO_CLIENT_ID  = '35247';
const MIKADO_PASSWORD   = 'eldarasanow';

// URL веб-сервисов Mikado
const MIKADO_SEARCH_URL = 'https://polomkam.net/ws1/service.asmx/Code_Search';
const MIKADO_INFO_URL   = 'https://polomkam.net/ws1/service.asmx/Code_Info';

// SOAP-корзина и история
const MIKADO_BASKET_URL    = 'https://polomkam.net/ws1/basket.asmx';
const MIKADO_DELIVERIES_URL = 'https://polomkam.net/ws1/deliveries.asmx';

// Админ-пользователь
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'adminpass';

// Инициализация корзины в сессии
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Функции работы с корзиной
function addToCart(string $zakaz, string $name, float $price): void {
    if (!isset($_SESSION['cart'][$zakaz])) {
        $_SESSION['cart'][$zakaz] = ['zakaz'=>$zakaz,'name'=>$name,'price'=>$price,'qty'=>1];
    } else {
        $_SESSION['cart'][$zakaz]['qty']++;
    }
}

function removeFromCart(string $zakaz): void {
    unset($_SESSION['cart'][$zakaz]);
}

function clearCart(): void {
    $_SESSION['cart'] = [];
}

function cartTotal(): float {
    $sum = 0.0;
    foreach ($_SESSION['cart'] as $item) {
        $sum += $item['price'] * $item['qty'];
    }
    return $sum;
}

// Функция поиска запчастей Code_Search
function callSearch(string $code) {
    $params = http_build_query([
        'Search_Code'   => $code,
        'ClientID'      => MIKADO_CLIENT_ID,
        'Password'      => MIKADO_PASSWORD,
        'FromStockOnly' => 'FromStockOnly'
    ]);
    $resp = @file_get_contents(MIKADO_SEARCH_URL . '?' . $params);
    if (!$resp) return [];
    $xml = @simplexml_load_string($resp);
    return $xml->List->Code_List_Row ?? [];
}

// Функция получения деталей Code_Info
function callInfo(string $zakaz) {
    $params = http_build_query([
        'ZakazCode' => $zakaz,
        'ClientID'  => MIKADO_CLIENT_ID,
        'Password'  => MIKADO_PASSWORD
    ]);
    $resp = @file_get_contents(MIKADO_INFO_URL . '?' . $params);
    if (!$resp) return [];
    $xml = @simplexml_load_string($resp);
    return $xml->List->Code_Info_Line ?? [];
}

?>
