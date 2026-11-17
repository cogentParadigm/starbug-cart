<?php
namespace Starbug\Cart\Operation;

use Starbug\Db\DatabaseInterface;
use Starbug\Bundle\BundleInterface;
use Starbug\Cart\Cart;
use Starbug\Db\Operation\Save;

class RemoveProduct extends Save {
  protected $model = "product_lines";
  protected $line;
  public function __construct(
    DatabaseInterface $db,
    protected Cart $cart
  ) {
    parent::__construct($db);
  }
  public function handle(array $data, BundleInterface $state): BundleInterface {
    $line = $this->db->query("product_lines")
      ->innerJoin("lines")->on("lines.id=product_lines.lines_id")
      ->condition("product_lines.id", $data["id"])
      ->condition("lines.orders_id", $this->cart->get("id"))->one();
    if (!empty($line)) {
      $this->db->remove("lines", ["id" => $line["lines_id"]]);
      $this->db->query("lines")
        ->condition("lines.orders_id", $this->cart->get("id"))
        ->condition("lines.type", ["shipping_lines", "tax_lines"])
        ->delete();
      $this->db->store("orders", ["id" => $this->cart->get("id"), "shipping_address" => "NULL"]);
    } else {
      $this->db->error("Item not found in cart", "global", "product_lines");
    }
    return $this->getErrorState($state);
  }
}
