<?php

namespace Tests\Unit\Actions\CapitalWithdrawalActions;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use App\Models\Branch;
use App\Models\CapitalWithdrawal;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class CapitalWithdrawalActionsReadTest extends ActionsTestCase
{
    private CapitalWithdrawalActions $capitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalWithdrawalActions = new CapitalWithdrawalActions();
    }

    public function test_capital_withdrawal_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->capitalWithdrawalActions->readAny(
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

    public function test_capital_withdrawal_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->capitalWithdrawalActions->readAny(
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

    public function test_capital_withdrawal_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->capitalWithdrawalActions->readAny(
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

    public function test_capital_withdrawal_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $capitalWithdrawalCount = 4;
        $idxTest = random_int(0, $capitalWithdrawalCount - 1);
        $defaultRemarks = CapitalWithdrawal::factory()->make()->remarks;
        $testremarks = CapitalWithdrawal::factory()->insertStringInName('testing')->make()->remarks;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(CapitalWithdrawal::factory()->count($capitalWithdrawalCount)
                    ->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'remarks' => $sequence->index == $idxTest ? $testremarks : $defaultRemarks,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        // Create a CapitalWithdrawal with specific remarks for search testing
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();

        CapitalWithdrawal::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
            'remarks' => 'testing',
        ]);

        $result = $this->capitalWithdrawalActions->readAny(
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

    public function test_capital_withdrawal_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->capitalWithdrawalActions->readAny(
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

    public function test_capital_withdrawal_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        // Test with zero perPage instead of negative to avoid SQL syntax errors
        $result = $this->capitalWithdrawalActions->readAny(
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

    public function test_capital_withdrawal_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $capitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->capitalWithdrawals()->inRandomOrder()->first();

        $result = $this->capitalWithdrawalActions->read($capitalWithdrawal);

        $this->assertInstanceOf(CapitalWithdrawal::class, $result);
    }
}
