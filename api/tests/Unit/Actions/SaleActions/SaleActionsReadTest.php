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
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class SaleActionsReadTest extends ActionsTestCase
{
    private SaleActions $saleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleActions = new SaleActions();
    }

    public function test_sale_actions_call_read_any_with_paginate_true_expect_paginator_object()
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

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->saleActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: 1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_sale_actions_call_read_any_with_paginate_false_expect_collection_object()
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

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->saleActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: false,
            page: null,
            perPage: null,
            limit: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_sale_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->saleActions->readAny(
            companyId: $maxId,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: false,
            page: null,
            perPage: null,
            limit: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_sale_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $saleCount = 4;
        $idxTest = random_int(0, $saleCount - 1);
        $defaultRemarks = Sale::factory()->make()->remarks;
        $testremarks = Sale::factory()->insertStringInName('testing')->make()->remarks;

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
                        ->state(new Sequence(
                            fn (Sequence $sequence) => [
                                'remarks' => $sequence->index == $idxTest ? $testremarks : $defaultRemarks,
                            ]
                        ))
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->create(['branch_id' => $branch->id]);
        $customer = Customer::factory()->for($company)->create();
        $customerAddress = CustomerAddress::factory()->for($company)->for($customer)->create();
        $salesOrder = SalesOrder::factory()->for($company)->for($branch)->for($customer)->for($customerAddress)->create();

        Sale::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $salesOrder->id,
            'remarks' => $testremarks,
        ]);

        $result = $this->saleActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: 'testing',

            paginate: true,
            page: 1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() >= 1);
    }

    public function test_sale_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_sale_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_sale_actions_call_read_expect_object()
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

        $result = $this->saleActions->read($sale);

        $this->assertInstanceOf(Sale::class, $result);
    }
}
