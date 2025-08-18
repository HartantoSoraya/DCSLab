<?php

namespace Tests\Unit\Actions\CustomerAddressActions;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Tests\ActionsTestCase;

class CustomerAddressActionsDeleteTest extends ActionsTestCase
{
    private CustomerAddressActions $customerAddressActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAddressActions = new CustomerAddressActions();
    }

    public function test_customer_address_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(
                    CustomerAddress::factory()->state(function (array $attributes, Company $company) {
                        $customer = Customer::factory()->for($company)->create();

                        return [
                            'customer_id' => $customer->id,
                        ];
                    })
                )
            )
            ->create();

        $customerAddress = $user->companies()->inRandomOrder()->first()
            ->customerAddresses()->inRandomOrder()->first();
        $result = $this->customerAddressActions->delete($customerAddress);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customer_addresses', [
            'id' => $customerAddress->id,
        ]);
    }
}
