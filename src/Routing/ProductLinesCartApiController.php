<?php
namespace Starbug\Cart\Routing;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Starbug\Cart\Cart;
use Starbug\Cart\Repository\ProductsRepository;
use Starbug\Cart\Repository\ShipmentsRepository;
use Starbug\Cart\Repository\TaxesRepository;
use Starbug\Price\FormatterInterface;
use Starbug\Routing\Controller;
use Starbug\Settings\SettingsInterface;

class ProductLinesCartApiController extends Controller {
  public function __construct(
    protected ProductsRepository $productLines,
    protected ShipmentsRepository $shippingLines,
    protected TaxesRepository $taxLines,
    protected ResponseFactoryInterface $response,
    protected FormatterInterface $price,
    protected Cart $cart,
    protected SettingsInterface $settings
  ) {
  }
  public function __invoke(): ResponseInterface {
    $order = $this->cart->getOrder();
    $products = $this->productLines->getProducts();
    $shipments = $this->shippingLines->getShipments();
    $taxes = $this->taxLines->getTaxes();
    $count = count($products);
    $total = $subtotal = array_sum(array_column($products, "total"));
    $shipping = "tbd";
    $tax = "tbd";
    $pickupAddress = $this->settings->get("pickup_address");
    if (!empty($pickupAddress) && !empty($order["shipping_address"]) && $order["shipping_address"] == $pickupAddress) {
      $shipping = "N/A";
    }
    if (!empty($shipments)) {
      $shipping = array_sum(array_column($shipments, "total"));
      $total += $shipping;
      $shipping = $this->price->format($shipping);
    }
    if (!empty($taxes)) {
      $tax = array_sum(array_column($taxes, "total"));
      $total += $tax;
      $tax = $this->price->format($tax);
    }
    return $this->response->createResponse()
      ->withHeader("Content-Type", "application/json")
      ->withHeader("Content-Range", "items 0-$count/$count")
      ->withHeader("Cart-Subtotal", $this->price->format($subtotal))
      ->withHeader("Cart-Shipping", $shipping)
      ->withHeader("Cart-Tax", $tax)
      ->withHeader("Cart-Total", $this->price->format($total))
      ->withBody(Utils::streamFor(json_encode($products)));
  }
}
