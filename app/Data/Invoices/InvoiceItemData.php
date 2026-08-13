<?php

namespace App\Data\Invoice;

final readonly class InvoiceItemData
{
    public function __construct(
        public string $name,
        public int $quantity,
        public string $price
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            quantity: $data['quantity'],
            price: $data['price']
        );
    }
}
