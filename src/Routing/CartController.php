<?php
namespace Starbug\Cart\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starbug\Cart\Repository\ProductsRepository;
use Starbug\Routing\Controller;

class CartController extends Controller {
  public function __construct(
    protected ProductsRepository $productLines
  ) {
  }
  public function __invoke(ServerRequestInterface $request): ResponseInterface {
    $products = $this->productLines->getProducts();
    $arguments = $request->getAttribute("route")->getOptions();
    $arguments["products"] = $products;
    $arguments["view"] = $arguments["view"] ?? "cart/default.html";
    $arguments["empty_view"] = $arguments["empty_view"] ?? "cart/empty.html";
    $view = empty($products) ? $arguments["empty_view"] : $arguments["view"];
    return $this->render($view, $arguments);
  }
}
