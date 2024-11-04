<?php

namespace App\Actions\Product;

use App\Actions\ProductUnit\ProductUnitActions;
use App\Models\Company;
use App\Models\Product;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Product
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $product = new Product();
            $product->company_id = $data['company_id'];
            $product->product_category_id = $data['product_category_id'];
            $product->brand_id = $data['brand_id'];
            $product->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $product->name = $data['name'];
            $product->product_type = $data['product_type'];
            $product->taxable_supply = $data['taxable_supply'];
            $product->standard_rated_supply = $data['standard_rated_supply'];
            $product->price_include_vat = $data['price_include_vat'];
            $product->point = $data['point'];
            $product->use_serial_number = $data['use_serial_number'];
            $product->has_expiry_date = $data['has_expiry_date'];
            $product->status = $data['status'];
            $product->remarks = $data['remarks'];
            $product->save();

            $this->saveProductUnit($product, $data['product_units']);

            DB::commit();

            $this->flushCache();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function saveProductUnit(Product $product, array $productUnits)
    {
        $productUnitActions = new ProductUnitActions();

        foreach ($productUnits as $productUnit) {
            $productUnitActions->create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'unit_id' => $productUnit['unit_id'],
                'code' => $productUnit['code'],
                'is_base' => $productUnit['is_base'],
                'conversion_value' => $productUnit['conversion_value'],
                'is_primary_unit' => $productUnit['is_primary_unit'],
                'remarks' => $productUnit['remarks'],
            ]);
        }
    }

    private function readAnyQuery(
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        ?int $limit
    ) {
        $query = Product::with('company', 'productCategory', 'brand', 'productUnits.unit')->withTrashed()
            ->withAggregate('company', 'name')
            ->where(function ($query) use ($withTrashed, $search, $companyId) {
                if ($withTrashed == true) {
                    $query = $query->withTrashed();
                } else {
                    $query = $query->withoutTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                $query->whereCompanyId($companyId);
            });

        $query->orderBy('company_name', 'asc')
            ->orderBy('name', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    public function readAny(
        ?bool $useCache,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        bool $paginate,
        ?int $page,
        ?int $perPage,
        ?int $limit
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = 'readAny_'.$companyId.'-'.$cacheSearch.'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) return $cacheResult;
            }

            $result = null;

            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,
                search: $search,
                companyId: $companyId,
                limit: $paginate ? null : $limit
            );

            if ($paginate) {
                $result = $query->paginate(perPage: $perPage, page: $page);
            } else {
                $result = $query->get();
            }

            $recordsCount = $result->count();

            if ($useCache === true) $this->saveToCache($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
        }
    }

    public function read(Product $product): Product
    {
        return $product->with('company', 'productCategory', 'brand', 'productUnits.unit')->first();
    }

    public function getAllActiveProduct(
        ?array $with,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,
        ?array $includeIds,

        ?int $limit
    ) {
        $timer_start = microtime(true);

        try {
            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,

                search: $search,
                companyId: $companyId,

                limit: $limit
            );

            if ($includeIds) {
                $query = $query->orWhereIn('id', $includeIds);

                $orders = $query->getQuery()->orders;
                $query->reorder();
                $query->orderByRaw('FIELD(id, '.implode(',', $includeIds).') desc');
                if (! empty($orders)) {
                    foreach ($orders as $order) {
                        $query->orderBy($order['column'], $order['direction']);
                    }
                }
            }

            return $query->get();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Product $product, array $data): Product
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $product->product_category_id = $data['product_category_id'];
            $product->brand_id = $data['brand_id'];
            $product->code = $this->generateUniqueCode($product->company_id, $data['code'], $product->id);
            $product->name = $data['name'];
            $product->product_type = $data['product_type'];
            $product->taxable_supply = $data['taxable_supply'];
            $product->standard_rated_supply = $data['standard_rated_supply'];
            $product->price_include_vat = $data['price_include_vat'];
            $product->point = $data['point'];
            $product->use_serial_number = $data['use_serial_number'];
            $product->has_expiry_date = $data['has_expiry_date'];
            $product->status = $data['status'];
            $product->remarks = $data['remarks'];
            $product->save();

            $this->updateProductUnit($product, $data['deleted_product_unit_ids'], $data['product_units']);

            DB::commit();

            $this->flushCache();

            return $product->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function updateProductUnit(Product $product, array $deletedProductUnitIds, array $productUnits)
    {
        $productUnitActions = new ProductUnitActions();
        foreach ($deletedProductUnitIds as $deletedProductUnitId) {
            $productUnitActions->delete($deletedProductUnitId, false);
        }

        foreach ($productUnits as $productUnit) {
            if (! $productUnit['id']) {
                $productUnitActions->create([
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'unit_id' => $productUnit['unit_id'],
                    'code' => $productUnit['code'],
                    'is_base' => $productUnit['is_base'],
                    'conversion_value' => $productUnit['conversion_value'],
                    'is_primary_unit' => $productUnit['is_primary_unit'],
                    'remarks' => $productUnit['remarks'],
                ], false);
            } else {
                $productUnitActions->update($productUnit['id'], [
                    'unit_id' => $productUnit['unit_id'],
                    'code' => $productUnit['code'],
                    'is_base' => $productUnit['is_base'],
                    'conversion_value' => $productUnit['conversion_value'],
                    'is_primary_unit' => $productUnit['is_primary_unit'],
                    'remarks' => $productUnit['remarks'],
                ], false);
            }
        }
    }

    public function delete(Product $product): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $product->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->products()->withTrashed()->count() + 1 + $tryCount;
                $code = 'PDT'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Product::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
