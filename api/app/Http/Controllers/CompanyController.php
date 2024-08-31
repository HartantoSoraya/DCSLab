<?php

namespace App\Http\Controllers;

use App\Actions\Company\CompanyActions;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends BaseController
{
    private $companyActions;

    public function __construct(CompanyActions $companyActions)
    {
        parent::__construct();

        $this->companyActions = $companyActions;
    }

    public function store(CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->create(
                user: Auth::user(),
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->readAny(
                user: Auth::user(),
                useCache: $request['refresh'],
                with: [],
                withTrashed: false,

                search: $request['search'],
                default: $request['default'],
                status: $request['status'],

                paginate: $request['paginate'],
                page: $request['page'],
                perPage: $request['per_page'],
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = CompanyResource::collection($result);

            return $response;
        }
    }

    public function read(Company $company, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->read($company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CompanyResource($result);

            return $response;
        }
    }

    public function getAllActiveCompany(Request $request)
    {
        $request = $request->validated();

        $result = $this->companyActions->getAllActiveCompany(
            user: Auth::user(),
            with: $request->has('with') ? explode(',', $request['with']) : [],
            search: $request['search'],
            includeIds: $request['includeIds'],
            limit: $request['limit']
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = CompanyResource::collection($result);

            return $response;
        }
    }

    public function getDefaultCompany()
    {
        $user = Auth::user();
        $defaultCompany = $this->companyActions->getDefaultCompany($user);

        return $defaultCompany->hId;
    }

    public function update(Company $company, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->update(
                user: Auth::user(),
                company: $company,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Company $company, CompanyRequest $companyRequest)
    {
        //Throw Error
        //throw New \Exception('Test Exception From Controller');

        //Throw Empty Response Error (HttpStatus 500)
        //return response()->error();

        //Custom Validation Error 1 Message (HttpStatus 422)
        //return response()->error('Custom Validation Error 1 Message', 422);

        //Custom Validation With Multiple Error (HttpStatus 422)
        //return response()->error(['name' => ['Custom Validation With Multiple Error'], 'address' => ['Custom Validation With Multiple Error']], 422);

        $result = false;
        $errorMsg = '';

        try {
            if ($this->companyActions->isDefaultCompany($company)) {
                return response()->error(trans('rules.company.delete_default_company'), 422);
            }

            $result = $this->companyActions->delete($company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
