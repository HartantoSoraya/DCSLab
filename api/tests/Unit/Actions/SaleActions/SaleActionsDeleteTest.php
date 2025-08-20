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
use Tests\ActionsTestCase;

class SaleActionsDeleteTest extends ActionsTestCase
{
    private SaleActions $saleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleActions = new SaleActions();
    }

    public function test_sale_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    Sale::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $warehouse = Warehouse::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $customer = Customer::factory()->for($company)->create();
                        $customerAddress = CustomerAddress::factory()->for($company)->for($customer)->create();
                        $salesOrder = SalesOrder::factory()->for($company)->for($branch)->for($customer)->for($customerAddress)->create();

                        return [
                            'branch_id' => $branch->id,
                            'warehouse_id' => $warehouse->id,
                            'customer_id' => $customer->id,
                            'sales_order_id' => $salesOrder->id,
                        ];
                    })
                )
            )->create();

        $sale = $user->companies()->inRandomOrder()->first()
            ->sales()->inRandomOrder()->first();
        $result = $this->saleActions->delete($sale);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sales', [
            'id' => $sale->id,
        ]);
    }
}
