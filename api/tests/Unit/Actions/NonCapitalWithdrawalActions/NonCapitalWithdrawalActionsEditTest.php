<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalActions;

use App\Actions\NonCapitalWithdrawal\NonCapitalWithdrawalActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\NonCapitalWithdrawal;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalActionsEditTest extends ActionsTestCase
{
    private NonCapitalWithdrawalActions $nonCapitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalActions = new NonCapitalWithdrawalActions();
    }

    public function test_non_capital_withdrawal_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalWithdrawal = $company->nonCapitalWithdrawals()->inRandomOrder()->first();

        $nonCapitalWithdrawalArr = NonCapitalWithdrawal::factory()->make()->toArray();
        $nonCapitalWithdrawalArr['branch_id'] = $nonCapitalWithdrawal->branch_id;
        $nonCapitalWithdrawalArr['category_id'] = $nonCapitalWithdrawal->category_id;
        $nonCapitalWithdrawalArr['cash_account_id'] = $nonCapitalWithdrawal->cash_account_id;

        $result = $this->nonCapitalWithdrawalActions->update($nonCapitalWithdrawal, $nonCapitalWithdrawalArr);

        $this->assertInstanceOf(NonCapitalWithdrawal::class, $result);
        $this->assertDatabaseHas('non_capital_withdrawals', [
            'id' => $nonCapitalWithdrawal->id,
            'company_id' => $nonCapitalWithdrawal->company_id,
            'code' => $nonCapitalWithdrawalArr['code'],
            'date' => $nonCapitalWithdrawalArr['date'],
            'category_id' => $nonCapitalWithdrawalArr['category_id'],
            'cash_account_id' => $nonCapitalWithdrawalArr['cash_account_id'],
            'amount' => $nonCapitalWithdrawalArr['amount'],
            'remarks' => $nonCapitalWithdrawalArr['remarks'],
        ]);
    }

    public function test_non_capital_withdrawal_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $category = NonCapitalWithdrawalCategory::factory()->for($company)->create();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);

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

        $nonCapitalWithdrawalArr = [];

        $this->nonCapitalWithdrawalActions->update($nonCapitalWithdrawal, $nonCapitalWithdrawalArr);
    }
}
