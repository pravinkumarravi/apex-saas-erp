<?php

namespace Modules\RestApi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\RestApi\Models\WebhookSubscription;

class RestApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $baseDir = dirname(__DIR__);

        // 1. Load Migrations
        $this->loadMigrationsFrom($baseDir . '/database/migrations');

        // 2. Load Views
        $viewsPath = $baseDir . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'rest-api');
            $this->loadViewsFrom($viewsPath, 'module-rest-api');
        }

        // 3. Register Web & API Routes
        $webRoutes = $baseDir . '/routes/web.php';
        if (file_exists($webRoutes)) {
            Route::middleware(['web', \App\Http\Middleware\TenantMiddleware::class])
                ->group($webRoutes);
        }

        $apiRoutes = $baseDir . '/routes/api.php';
        if (file_exists($apiRoutes)) {
            Route::prefix('api/v1')
                ->middleware(['api', \App\Http\Middleware\TenantMiddleware::class])
                ->group($apiRoutes);
        }

        // 4. Hook into system actions to trigger registered webhooks
        add_action('after_sales_order_created', function ($salesOrder) {
            $this->triggerWebhookForEvent('after_sales_order_created', [
                'event' => 'sales_order.created',
                'order_id' => $salesOrder->id,
                'so_number' => $salesOrder->so_number,
                'total_amount' => $salesOrder->total_amount,
                'timestamp' => now()->toIso8601String(),
            ]);
        });

        add_action('after_customer_created', function ($customer) {
            $this->triggerWebhookForEvent('after_customer_created', [
                'event' => 'customer.created',
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'timestamp' => now()->toIso8601String(),
            ]);
        });
    }

    protected function triggerWebhookForEvent(string $event, array $payload): void
    {
        try {
            $subscriptions = WebhookSubscription::where('is_active', true)->get();
            foreach ($subscriptions as $sub) {
                if ($sub->listensTo($event)) {
                    $sub->update(['last_dispatched_at' => now()]);
                    do_action('webhook_dispatched', $sub, $payload);
                }
            }
        } catch (\Throwable $e) {
            // Silently capture if table not yet migrated during initial boot
        }
    }
}
