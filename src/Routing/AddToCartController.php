<?php
namespace Starbug\Cart\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starbug\Cart\Operation\AddProduct;
use Starbug\Routing\Controller;

class AddToCartController extends Controller {
  public $model = "product_lines";
  public function __construct(
    protected AddProduct $add
  ) {
  }
  public function __invoke($view, ServerRequestInterface $request): ResponseInterface {
    $arguments = $request->getAttribute("route")->getOptions();
    $arguments["product"] = $this->add->getLine();
    return $this->render($view, $arguments);
  }
}
