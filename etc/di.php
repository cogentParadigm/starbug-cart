<?php
namespace Starbug\Cart;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starbug\Cart\Helper\CartHelper;
use Starbug\Cart\Routing\RouteProvider;
use Starbug\Http\UriBuilderInterface;

use function DI\add;
use function DI\autowire;
use function DI\get;

return [
  "route.providers" => add([
    get(RouteProvider::class)
  ]),
  "db.schema.migrations" => add([
    get(Migration::class)
  ]),
  "cart_token" => function (ContainerInterface $c) {
    $request = $c->get(ServerRequestInterface::class);
    $uri = $c->get(UriBuilderInterface::class);
    $cid = $request->getCookieParams()["cid"] ?? false;
    if (!$cid) {
      $cid = bin2hex(random_bytes(16));
      setcookie("cid", $cid, 0, $uri->build(""), null, false, false);
    }
    return ["token" => $cid];
  },
  Cart::class => autowire()->constructorParameter("conditions", get("cart_token")),
  "template.helpers" => add([
    "cart" => CartHelper::class
  ]),
];
