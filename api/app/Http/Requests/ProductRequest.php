<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use App\Enums\RecordStatus;
use App\Helpers\HashidsHelper;
use App\Models\Product;
use App\Rules\IsValidBrand;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProductCategory;
use App\Rules\ProductStoreValidCode;
use App\Rules\ProductUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $product = $this->route('product');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Product::class) ? true : false;
            case 'read':
                return $user->can('view', Product::class, $product) ? true : false;
            case 'store':
                return $user->can('create', Product::class) ? true : false;
            case 'update':
                return $user->can('update', Product::class, $product) ? true : false;
            case 'delete':
                return $user->can('delete', Product::class, $product) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return [
                    'refresh' => ['required', 'boolean'],
                    'with_trashed' => ['required', 'boolean'],

                    'search' => ['nullable', 'string'],
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'status' => ['nullable', 'integer', 'in:'.implode(',', RecordStatus::toArrayValue())],

                    'paginate' => ['required', 'boolean'],
                    'page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:1'],
                    'per_page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:10'],
                    'limit' => ['nullable', 'integer', 'min:1'],
                ];
            case 'read':
                return [];
            case 'store':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'product_category_id' => ['required', 'integer', new IsValidProductCategory()],
                    'brand_id' => ['required', 'integer', new IsValidBrand()],
                    'code' => ['required', 'string', 'max:255', new ProductStoreValidCode($this->company_id)],
                    'name' => ['required', 'string', 'max:255'],
                    'product_type' => ['required', 'integer', 'in:'.implode(',', ProductType::toArrayValue())],
                    'taxable_supply' => ['required', 'boolean'],
                    'standard_rated_supply' => ['required', 'numeric'],
                    'price_include_vat' => ['required', 'boolean'],
                    'point' => ['required', 'integer'],
                    'use_serial_number' => ['required', 'boolean'],
                    'has_expiry_date' => ['required', 'boolean'],
                    'status' => ['required', 'integer', 'in:'.implode(',', RecordStatus::toArrayValue())],
                    'remarks' => ['nullable', 'string', 'max:255'],

                    'product_units' => 'required|array',
                    'product_units.*.unit_id' => 'required|integer',
                    'product_units.*.code' => 'required|string',
                    'product_units.*.is_base' => 'required|boolean',
                    'product_units.*.conversion_value' => 'required|numeric',
                    'product_units.*.is_primary_unit' => 'required|boolean',
                    'product_units.*.remarks' => 'nullable|string',
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'product_category_id' => ['required', 'integer', new IsValidProductCategory()],
                    'brand_id' => ['required', 'integer', new IsValidBrand()],
                    'code' => ['required', 'string', 'max:255', new ProductUpdateValidCode($this->company_id, $this->route('product'))],
                    'name' => ['required', 'string', 'max:255'],
                    'product_type' => ['required', 'integer', 'in:'.implode(',', ProductType::toArrayValue())],
                    'taxable_supply' => ['required', 'boolean'],
                    'standard_rated_supply' => ['required', 'numeric'],
                    'price_include_vat' => ['required', 'boolean'],
                    'point' => ['required', 'integer'],
                    'use_serial_number' => ['required', 'boolean'],
                    'has_expiry_date' => ['required', 'boolean'],
                    'status' => ['required', 'integer', 'in:'.implode(',', RecordStatus::toArrayValue())],
                    'remarks' => ['nullable', 'string', 'max:255'],

                    'delete_product_unit_ids' => 'nullable|array',
                    'delete_product_unit_ids.*' => 'required|integer|exists:product_units,id',

                    'product_units' => 'required|array',
                    'product_units.*.unit_id' => 'required|integer',
                    'product_units.*.code' => 'required|string',
                    'product_units.*.is_base' => 'required|boolean',
                    'product_units.*.conversion_value' => 'required|numeric',
                    'product_units.*.is_primary_unit' => 'required|boolean',
                    'product_units.*.remarks' => 'nullable|string',
                ];
            case 'delete':
                return [

                ];
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.product.company'),
            'brand_id' => trans('validation_attributes.product.brand'),
            'product_category_id' => trans('validation_attributes.product.product_category'),
            'code' => trans('validation_attributes.product.code'),
            'name' => trans('validation_attributes.product.name'),
            'product_type' => trans('validation_attributes.product.product_type'),
            'taxable_supply' => trans('validation_attributes.product.taxable_supply'),
            'standard_rated_supply' => trans('validation_attributes.product.standard_rated_supply'),
            'price_include_vat' => trans('validation_attributes.product.price_include_vat'),
            'point' => trans('validation_attributes.product.point'),
            'use_serial_number' => trans('validation_attributes.product.use_serial_number'),
            'has_expiry_date' => trans('validation_attributes.product.has_expiry_date'),
            'status' => trans('validation_attributes.product.status'),
            'remarks' => trans('validation_attributes.product.remarks'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $this->merge([
                    'with_trashed' => $this->has('with_trashed') ? $this->with_trashed : null,

                    'search' => $this->has('search') ? $this->search : null,
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,

                    'page' => $this->has('page') ? $this->page : null,
                    'per_page' => $this->has('per_page') ? $this->per_page : null,
                    'limit' => $this->has('limit') ? $this->limit : null,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $brand_id = null;
                if ($this->has('brand_id')) {
                    if ($this->brand_id) {
                        $brand_id = Hashids::decode($this['brand_id'])[0];
                    }
                }
                $product_category_id = null;
                if ($this->has('product_category_id')) {
                    if ($this->product_category_id) {
                        $product_category_id = Hashids::decode($this['product_category_id'])[0];
                    }
                }

                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'product_category_id' => $product_category_id,
                    'brand_id' => $brand_id,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
