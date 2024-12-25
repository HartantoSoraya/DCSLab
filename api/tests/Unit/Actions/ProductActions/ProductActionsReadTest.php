<?php

namespace Tests\Unit\Actions\ProductActions;

use App\Actions\Product\ProductActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class ProductActionsReadTest extends ActionsTestCase
{
    private ProductActions $productActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productActions = new ProductActions();
    }

    public function test_product_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 5);
        for ($i = 0; $i < $productSeedCount; $i++) {
            $productCategory = $company->productCategories()->inRandomOrder()->first();

            $brand = $company->brands()->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productCategory)
                ->for($brand);

            $product->create();
        }

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productActions->readAny(
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

    public function test_product_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 5);
        for ($i = 0; $i < $productSeedCount; $i++) {
            $productCategory = $company->productCategories()->inRandomOrder()->first();

            $brand = $company->brands()->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productCategory)
                ->for($brand);

            $product->create();
        }

        $result = $this->productActions->readAny(
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

    public function test_product_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->productActions->readAny(
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

    public function test_product_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $productCount = 4;
        $idxTest = random_int(0, $productCount - 1);
        $defaultName = Product::factory()->make()->name;
        $testname = Product::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3))
                ->has(Product::factory()->count($productCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productActions->readAny(
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
        $this->assertTrue($result->total() == 1);
    }

    public function test_product_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_product_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_product_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productCategory = $company->productCategories()->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
            ->for($company)
            ->for($productCategory)
            ->for($brand);

        $product = $product->create();

        $result = $this->productActions->read($product);

        $this->assertInstanceOf(Product::class, $result);
    }
}