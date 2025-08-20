<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'due_days' => fake()->numberBetween(0, 30),
            'delivery_note_reference' => fake()->sentence(),

            'tax_invoice_number' => fake()->sentence(),
            'tax_invoice_vat_base' => fake()->numberBetween(0, 1000000),
            'tax_invoice_vat' => fake()->numberBetween(0, 1000000),
            'return_tax_invoice_number' => fake()->sentence(),
            'return_tax_invoice_vat_base' => fake()->numberBetween(0, 1000000),
            'return_tax_invoice_vat' => fake()->numberBetween(0, 1000000),

            'remarks' => fake()->sentence(),
            'is_posted' => fake()->boolean(),

            'total' => fake()->numberBetween(0, 1000000),
            'global_discount_rate' => fake()->numberBetween(0, 1000000),
            'global_discount_fixed' => fake()->numberBetween(0, 1000000),
            'additional_cost' => fake()->numberBetween(0, 1000000),
            'rounding' => fake()->numberBetween(0, 1000000),
            'grand_total' => fake()->numberBetween(0, 1000000),

            'return_total' => fake()->numberBetween(0, 1000000),
            'return_global_discount_rate' => fake()->numberBetween(0, 1000000),
            'return_global_discount_fixed' => fake()->numberBetween(0, 1000000),
            'return_rounding' => fake()->numberBetween(0, 1000000),
            'return_grand_total' => fake()->numberBetween(0, 1000000),

            'amount_due' => fake()->numberBetween(0, 1000000),
            'amount_paid_by_sale_order_down_payment' => fake()->numberBetween(0, 1000000),
            'amount_paid_by_sale_return' => fake()->numberBetween(0, 1000000),
            'amount_paid_before_invoice' => fake()->numberBetween(0, 1000000),
            'amount_paid_on_invoice' => fake()->numberBetween(0, 1000000),
            'amount_paid_after_invoice' => fake()->numberBetween(0, 1000000),
            'amount_paid_total' => fake()->numberBetween(0, 1000000),
            'amount_due' => fake()->numberBetween(0, 1000000),

            'is_paid_off' => fake()->boolean(),
            'is_valid' => fake()->boolean(),
        ];
    }

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'remarks' => $str.' '.fake()->sentence(),
            ];
        });
    }
}
