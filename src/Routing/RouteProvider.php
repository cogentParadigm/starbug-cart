<?php
namespace Starbug\Cart\Routing;

use Starbug\Cart\Operation\AddProduct;
use Starbug\Cart\Operation\RemoveProduct;
use Starbug\Cart\Operation\UpdateQty;
use Starbug\Routing\RouteProviderInterface;
use Starbug\Routing\Route;

class RouteProvider implements RouteProviderInterface {

  public function configure(Route $routes) {
    $routes->addRoute("cart/add[.{format:xhr}]", AddToCartController::class, [
      "view" => "cart/add.html",
      "title" => "Add to Cart"
    ])
    ->onPost(AddProduct::class);
    $routes->addRoute("cart", CartController::class);
    $routes->addRoute("api/product-lines/cart.{format:json}", ProductLinesCartApiController::class)
      ->onPost(UpdateQty::class)
      ->onDelete(RemoveProduct::class);
  }
}
