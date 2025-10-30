<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::withCount('products')
            ->latest('starts_at')
            ->latest()
            ->paginate(12);

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        $products = Product::where('status', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $defaultStart = Carbon::now()->startOfDay();
        $defaultEnd = $defaultStart->copy()->addWeek()->endOfDay();

        $promotion = new Promotion([
            'discount_type' => Promotion::TYPE_PERCENTAGE,
            'audience_type' => Promotion::AUDIENCE_ALL,
            'starts_at' => $defaultStart,
            'ends_at' => $defaultEnd,
        ]);

        return view('admin.promotions.create', [
            'promotion' => $promotion,
            'products' => $products,
            'selectedProducts' => $products->pluck('id')->all(),
            'users' => $users,
            'selectedUsers' => [],
        ]);
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $productIds = collect($data['product_ids'] ?? [])->unique()->values()->all();
        $userIds = collect($data['user_ids'] ?? [])->unique()->values()->all();
        unset($data['product_ids']);
        unset($data['user_ids']);
        $payload = $this->normalizeDates($data);

        try {
            DB::transaction(function () use ($payload, $productIds, $userIds) {
                $promotion = Promotion::create($payload);
                $promotion->products()->sync($productIds);
                $promotion->users()->sync($userIds);
            });

            return redirect()->route('promotions.index')
                ->with('success', 'Promo berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('Failed to create promotion: ' . $e->getMessage(), ['exception' => $e]);

            return back()->withInput()->with('error', 'Gagal membuat promo. Silakan coba lagi.');
        }
    }

    public function edit(Promotion $promotion): View
    {
        $promotion->load(['products', 'users']);
        $products = Product::where('status', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.promotions.edit', [
            'promotion' => $promotion,
            'products' => $products,
            'selectedProducts' => $promotion->products->pluck('id')->all(),
            'users' => $users,
            'selectedUsers' => $promotion->users->pluck('id')->all(),
        ]);
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $data = $request->validated();
        $productIds = collect($data['product_ids'] ?? [])->unique()->values()->all();
        $userIds = collect($data['user_ids'] ?? [])->unique()->values()->all();
        unset($data['product_ids']);
        unset($data['user_ids']);
        $payload = $this->normalizeDates($data);

        try {
            DB::transaction(function () use ($promotion, $payload, $productIds, $userIds) {
                $promotion->update($payload);
                $promotion->products()->sync($productIds);
                $promotion->users()->sync($userIds);
            });

            return redirect()->route('promotions.index')
                ->with('success', 'Promo berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Failed to update promotion: ' . $e->getMessage(), ['exception' => $e, 'promotion_id' => $promotion->getKey()]);

            return back()->withInput()->with('error', 'Gagal memperbarui promo. Silakan coba lagi.');
        }
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        try {
            $promotion->delete();

            return redirect()->route('promotions.index')
                ->with('success', 'Promo berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete promotion: ' . $e->getMessage(), ['exception' => $e, 'promotion_id' => $promotion->getKey()]);

            return redirect()->route('promotions.index')
                ->with('error', 'Gagal menghapus promo.');
        }
    }

    protected function normalizeDates(array $data): array
    {
        if (! empty($data['starts_at'])) {
            $data['starts_at'] = Carbon::parse($data['starts_at'])->startOfMinute();
        }

        if (! empty($data['ends_at'])) {
            $data['ends_at'] = Carbon::parse($data['ends_at'])->endOfMinute();
        }

        return $data;
    }
}
