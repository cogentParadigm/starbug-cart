<?php
namespace Starbug\Cart;

use Starbug\Db\Schema\AbstractMigration;

class Migration extends AbstractMigration {
  public function up() {
    $this->schema->addTable(
      "orders",
      ["token", "type" => "string", "length" => "128", "default" => ""],
    );

    $this->schema->addTable("address",
      ["order_token", "type" => "string", "default" => ""],
    );
  }
}
