<?php

namespace Tests\Unit\Actions\SaleActions;

use App\Actions\Sale\SaleActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Sale;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Tests\ActionsTestCase;

class SaleActionsCreateTest extends ActionsTestCase
{
    private SaleActions $saleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleActions = new SaleActions();
    }

    public function test_sale_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->create(['branch_id' => $branch->id]);
        $customer = Customer::factory()->for($company)->create();
        $customerAddress = CustomerAddress::factory()->for($company)->for($customer)->create();
        $salesOrder = SalesOrder::factory()->for($company)->for($branch)->for($customer)->for($customerAddress)->create();

        $saleArr = Sale::factory()->for($company)->make()->toArray();
        $saleArr['branch_id'] = $branch->id;
        $saleArr['warehouse_id'] = $warehouse->id;
        $saleArr['customer_id'] = $customer->id;
        $saleArr['customer_address_id'] = $customerAddress->id;
        $saleArr['sales_order_id'] = $salesOrder->id;

        $result = $this->saleActions->create($saleArr);

        $this->assertDatabaseHas('sales', [
            'id' => $result->id,
            'company_id' => $saleArr['company_id'],
            'branch_id' => $saleArr['branch_id'],
            'code' => $saleArr['code'],
            'date' => $saleArr['date'],
            'due_days' => $saleArr['due_days'],
            'warehouse_id' => $saleArr['warehouse_id'],
            'customer_id' => $saleArr['customer_id'],
            'sales_order_id' => $saleArr['sales_order_id'],
            'delivery_note_reference' => $saleArr['delivery_note_reference'],
            'tax_invoice_number' => $saleArr['tax_invoice_number'],
            'tax_invoice_vat_base' => $saleArr['tax_invoice_vat_base'],
            'tax_invoice_vat' => $saleArr['tax_invoice_vat'],
            'return_tax_invoice_number' => $saleArr['return_tax_invoice_number'],
            'return_tax_invoice_vat_base' => $saleArr['return_tax_invoice_vat_base'],
            'return_tax_invoice_vat' => $saleArr['return_tax_invoice_vat'],
            'remarks' => $saleArr['remarks'],
            'is_posted' => $saleArr['is_posted'],
            'total' => $saleArr['total'],
            'global_discount_rate' => $saleArr['global_discount_rate'],
            'global_discount_fixed' => $saleArr['global_discount_fixed'],
            'rounding' => $saleArr['rounding'],
            'grand_total' => $saleArr['grand_total'],
            'return_total' => $saleArr['return_total'],
            'return_global_discount_rate' => $saleArr['return_global_discount_rate'],
            'return_global_discount_fixed' => $saleArr['return_global_discount_fixed'],
            'return_rounding' => $saleArr['return_rounding'],
            'return_grand_total' => $saleArr['return_grand_total'],
            'amount_due' => $saleArr['amount_due'],
            'amount_paid_by_sale_order_down_payment' => $saleArr['amount_paid_by_sale_order_down_payment'],
            'amount_paid_by_sale_return' => $saleArr['amount_paid_by_sale_return'],
            'amount_paid_before_invoice' => $saleArr['amount_paid_before_invoice'],
            'amount_paid_on_invoice' => $saleArr['amount_paid_on_invoice'],
            'amount_paid_after_invoice' => $saleArr['amount_paid_after_invoice'],
            'amount_paid_total' => $saleArr['amount_paid_total'],
            'is_paid_off' => $saleArr['is_paid_off'],
            'is_valid' => $saleArr['is_valid'],
        ]);
    }

    public function test_sale_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleActions->create([]);
    }
}
