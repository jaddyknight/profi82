<?php
// diagnose_basket_add.php - вывод детальных SOAP-запросов/ответов
require 'config.php';

// Тестовая позиция
$test = ['zakaz'=>'xsct-sm121','qty'=>1];
$url='https://polomkam.net/ws1/basket.asmx';
$action='http://mikado-parts.ru/ws1/Basket_Add';

$xmlRequest = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <Basket_Add xmlns="http://mikado-parts.ru/ws1/">
      <ZakazCode>{$test['zakaz']}</ZakazCode>
      <QTY>{$test['qty']}</QTY>
      <DeliveryType>0</DeliveryType>
      <Notes></Notes>
      <ClientID>{MIKADO_CLIENT_ID}</ClientID>
      <Password>{MIKADO_PASSWORD}</Password>
      <ExpressID>0</ExpressID>
      <StockID>1</StockID>
    </Basket_Add>
  </soap:Body>
</soap:Envelope>
XML;

echo "<h3>SOAP REQUEST:</h3><pre>".htmlspecialchars($xmlRequest)."</pre>";

$ch=curl_init($url);
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>[
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: "'.$action.'"'
    ],
    CURLOPT_POSTFIELDS=>$xmlRequest
]);
$response=curl_exec($ch);
if(curl_errno($ch)){
    echo "<h3>CURL ERROR:</h3><pre>".curl_error($ch)."</pre>";
} else {
    echo "<h3>SOAP RESPONSE:</h3><pre>".htmlspecialchars($response)."</pre>";
}
curl_close($ch);

// Пробуем парсинг
libxml_use_internal_errors(true);
$xml=new SimpleXMLElement($response);
$xml->registerXPathNamespace('soap','http://schemas.xmlsoap.org/soap/envelope/');
$xml->registerXPathNamespace('ns','http://mikado-parts.ru/ws1/');
$nodes=$xml->xpath('//soap:Body/ns:Basket_AddResponse/ns:Basket_AddResult');
echo "<h3>PARSE NODES:</h3><pre>".print_r($nodes,true)."</pre>";
if($nodes){
    $res=$nodes[0];
    echo "<h3>Parsed Message:</h3>".(string)$res->Message;
    echo "<h3>Parsed ID:</h3>".(string)$res->ID;
}
