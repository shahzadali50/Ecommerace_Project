<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'csrf_token' => csrf_token(),
            // 'translations' => __('messages'), // ✅ will now be globally available
            'currentLocale' => app()->getLocale(),
             // ✅ Share wishlist globally
            'wishlist' => fn () => Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')
            : [],
            // ✅ Share categories globally
            'global_categories' =>fn () => Category::whereHas('products')
            ->select('id', 'name', 'slug')
            ->orderBy('id')
            ->get(),
            // ✅ Share brands globally
            'global_brands' => fn () => Brand::whereHas('products')
            ->select('id', 'name', 'slug')
            ->orderBy('id')
            ->get(),
        ];
    }
}
