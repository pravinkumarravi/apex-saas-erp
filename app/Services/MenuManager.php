<?php

namespace App\Services;

use App\Helpers\MenuHelper;

class MenuManager
{
    protected array $customItems = [];
    protected array $customChildren = [];
    protected bool $initialized = false;

    /**
     * Add a sidebar menu item.
     */
    public function addSidebarMenuItem(string $slug, array $item): void
    {
        $this->customItems[$slug] = array_merge([
            'slug' => $slug,
            'name' => $item['name'] ?? ucfirst($slug),
            'icon' => $item['icon'] ?? 'settings',
            'path' => $item['path'] ?? '#',
            'order' => $item['order'] ?? $item['position'] ?? 50,
            'group' => $item['group'] ?? 'ERP Modules',
            'subItems' => $item['subItems'] ?? [],
        ], $item);
    }

    /**
     * Add a child item under an existing sidebar parent.
     */
    public function addSidebarChildItem(string $parentSlug, array $subItem): void
    {
        $this->customChildren[$parentSlug][] = [
            'name' => $subItem['name'] ?? 'Sub Item',
            'path' => $subItem['path'] ?? $subItem['href'] ?? '#',
            'order' => $subItem['order'] ?? $subItem['position'] ?? 10,
        ];
    }

    /**
     * Build and return all menu groups, processed via hooks and filters.
     */
    public function getMenuGroups(): array
    {
        // 1. Fire initialization action hook so modules can register menu items (e.g. admin_init or app_menu_init)
        if (!$this->initialized) {
            do_action('admin_init');
            do_action('app_menu_init', $this);
            $this->initialized = true;
        }

        // 2. Base ERP menu groups
        $mainNav = MenuHelper::getMainNavItems();
        $others = MenuHelper::getOthersItems();

        // 3. Inject custom children into main nav if parent matches
        foreach ($this->customChildren as $parentSlug => $children) {
            foreach ($mainNav as &$navItem) {
                if (strtolower($navItem['name']) === strtolower($parentSlug) ||
                    str_contains(strtolower($navItem['name']), strtolower($parentSlug))) {
                    $navItem['subItems'] = array_merge($navItem['subItems'] ?? [], $children);
                }
            }
        }

        // 4. Inject custom standalone items
        foreach ($this->customItems as $item) {
            $groupName = $item['group'] ?? 'ERP Modules';
            if ($groupName === 'ERP Modules') {
                $mainNav[] = [
                    'icon' => $item['icon'],
                    'name' => $item['name'],
                    'subItems' => !empty($item['subItems']) ? $item['subItems'] : [
                        ['name' => $item['name'], 'path' => $item['path']]
                    ],
                ];
            } else {
                $others[] = [
                    'icon' => $item['icon'],
                    'name' => $item['name'],
                    'subItems' => !empty($item['subItems']) ? $item['subItems'] : [
                        ['name' => $item['name'], 'path' => $item['path']]
                    ],
                ];
            }
        }

        $groups = [
            [
                'title' => 'ERP Modules',
                'items' => $mainNav,
            ],
            [
                'title' => 'System & Controls',
                'items' => $others,
            ],
        ];

        // 5. Append dynamic modules from ModuleManager if any
        try {
            $moduleManager = app(ModuleManager::class);
            $moduleMenus = $moduleManager->getModuleMenuItems();

            if (!empty($moduleMenus)) {
                $moduleNavItems = [];
                foreach ($moduleMenus as $m) {
                    $item = [
                        'icon' => $m['icon'] ?? 'settings',
                        'name' => $m['name'] ?? $m['title'] ?? 'Module',
                    ];

                    if (!empty($m['subItems'])) {
                        $item['subItems'] = $m['subItems'];
                    } else {
                        $path = $m['path'] ?? null;
                        if (!$path && !empty($m['route']) && \Illuminate\Support\Facades\Route::has($m['route'])) {
                            $path = route($m['route']);
                        }
                        $item['path'] = $path ?? '#';
                    }

                    $moduleNavItems[] = $item;
                }

                if (!empty($moduleNavItems)) {
                    $groups[] = [
                        'title' => 'Installed Extensions & Modules',
                        'items' => $moduleNavItems,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback during early boot
        }

        // 6. Apply filter hook
        $groups = apply_filters('sidebar_menu', $groups);

        return $groups;
    }

    /**
     * Reset items for testing.
     */
    public function reset(): void
    {
        $this->customItems = [];
        $this->customChildren = [];
        $this->initialized = false;
    }
}
