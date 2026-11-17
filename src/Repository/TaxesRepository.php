<?php
namespace Starbug\Cart\Repository;

use Starbug\Cart\Cart;
use Starbug\Db\DatabaseInterface;
use Starbug\Db\Query\EntityInterface;
use Starbug\Price\FormatterInterface;

class TaxesRepository {
  public function __construct(
    protected Cart $cart,
    protected EntityInterface $entity,
    protected DatabaseInterface $db,
    protected FormatterInterface $price
  ) {
  }
  public function getTaxes($conditions = []) {
    $order = $this->cart->getOrder();
    if (empty($order)) {
      return [];
    }
    $lines = $this->db->query("tax_lines")
      ->innerJoin("lines")->on("lines.id=tax_lines.lines_id")
      ->condition("lines.orders_id", $order["id"])
      ->select("lines.*")
      ->select("tax_lines.*");
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
  public function addTax($tax) {
    $lines = $this->getTaxes();
    $this->cart->init();
    $order = $this->cart->getOrder();
    $line = [
      "orders_id" => $order["id"],
      "type" => "tax_lines",
      "description" => $tax["description"] ?? "Sales Tax",
      "price" => $tax["price"],
      "taxable" => "0",
      "qty" => 1
    ] + $tax;
    if (!empty($lines)) {
      $line["id"] = $lines[0]["id"];
    }
    $this->entity->store("tax_lines", $line);
    if (empty($line["id"])) {
      $line['id'] = $this->db->getInsertId("tax_lines");
    }
    return $line;
  }
}
