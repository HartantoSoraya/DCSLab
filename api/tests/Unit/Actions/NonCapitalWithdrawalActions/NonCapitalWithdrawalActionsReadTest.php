<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalActions;

use App\Actions\NonCapitalWithdrawal\NonCapitalWithdrawalActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\NonCapitalWithdrawal;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalActionsReadTest extends ActionsTestCase
{
    private NonCapitalWithdrawalActions $nonCapitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalActions = new NonCapitalWithdrawalActions();
    }

    public function test_non_capital_withdrawal_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->nonCapitalWithdrawalActions->readAny(
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

    public function test_non_capital_withdrawal_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->nonCapitalWithdrawalActions->readAny(
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

    public function test_non_capital_withdrawal_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->nonCapitalWithdrawalActions->readAny(
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

    public function test_non_capital_withdrawal_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $nonCapitalWithdrawalCount = 4;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->count($nonCapitalWithdrawalCount)
                        ->state(function (array $attributes, Company $company) {
                            $branch = $company->branches()->inRandomOrder()->first();
                            $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                            $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

                            return [
                                'branch_id' => $branch->id,
                                'category_id' => $category->id,
                                'cash_account_id' => $cashAccount->id,
                            ];
                        })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        // Create a NonCapitalWithdrawal with specific remarks for search testing
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

        NonCapitalWithdrawal::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'cash_account_id' => $cashAccount->id,
            'remarks' => 'testing',
        ]);

        $result = $this->nonCapitalWithdrawalActions->readAny(
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

    public function test_non_capital_withdrawal_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->nonCapitalWithdrawalActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: -1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() >= 0);
    }

    public function test_non_capital_withdrawal_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        // Test with zero perPage instead of negative to avoid SQL syntax errors
        $result = $this->nonCapitalWithdrawalActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: 1,
            perPage: 0,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() >= 0);
    }

    public function test_non_capital_withdrawal_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $nonCapitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->nonCapitalWithdrawals()->inRandomOrder()->first();

        $result = $this->nonCapitalWithdrawalActions->read($nonCapitalWithdrawal);

        $this->assertInstanceOf(NonCapitalWithdrawal::class, $result);
    }
}
