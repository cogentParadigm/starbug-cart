<?php
namespace Starbug\Cart\Repository;

use Starbug\Cart\Cart;
use Starbug\Db\DatabaseInterface;
use Starbug\Db\Query\EntityInterface;
use Starbug\Price\FormatterInterface;

class ShipmentsRepository {
  public function __construct(
    protected Cart $cart,
    protected EntityInterface $entity,
    protected DatabaseInterface $db,
    protected FormatterInterface $price
  ) {
  }
  public function getShipments($conditions = []) {
    $order = $this->cart->getOrder();
    if (empty($order)) {
      return [];
    }
    $lines = $this->db->query("shipping_lines")
      ->innerJoin("lines")->on("lines.id=shipping_lines.lines_id")
      ->condition("lines.orders_id", $order["id"])
      ->select("lines.*")
      ->select("shipping_lines.*");
    if (!empty($conditions)) {
      $lines->conditions($conditions);
    }
    $lines = $lines->all();
    foreach ($lines as &$line) {
      $line["total"] = $line["price"] * $line["qty"];
      $line["total_formatted"] = $this->price->format($line["total"]);
      $line["price_formatted"] = $this->price->format($line["price"]);
    }
    return $lines;
  }
  public function addShipment($method, $replace = true) {
    $this->cart->init();
    $order = $this->cart->getOrder();
    $line = [
      "orders_id" => $order["id"],
      "type" => "shipping_lines",
      "method" => $method["id"],
      "description" => $method["name"],
      "price" => (string) $method["price"],
      "taxable" => $method["taxable"] ?? "0",
      "qty" => 1
    ];
    $lines = $this->getShipments();
    if ($replace && !empty($lines)) {
      $line["id"] = $lines[0]["id"];
    }
    $this->entity->store("shipping_lines", $line);
    if (empty($line["id"])) {
      $line['id'] = $this->db->getInsertId("shipping_lines");
    }
    return $line;
  }
}
