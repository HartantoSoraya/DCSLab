<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalActions;

use App\Actions\NonCapitalWithdrawal\NonCapitalWithdrawalActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\NonCapitalWithdrawal;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalActionsDeleteTest extends ActionsTestCase
{
    private NonCapitalWithdrawalActions $nonCapitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalActions = new NonCapitalWithdrawalActions();
    }

    public function test_non_capital_withdrawal_actions_call_delete_expect_bool()
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

        $nonCapitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->nonCapitalWithdrawals()->inRandomOrder()->first();
        $result = $this->nonCapitalWithdrawalActions->delete($nonCapitalWithdrawal);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('non_capital_withdrawals', [
            'id' => $nonCapitalWithdrawal->id,
        ]);
    }
}
