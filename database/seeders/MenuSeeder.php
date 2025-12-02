<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('menu_user_type')->truncate();
        DB::table('menus')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get user types
        $adminType = UserType::where('name', 'Admin')->first();
        $managerType = UserType::where('name', 'Manager')->first();
        $operationsType = UserType::where('name', 'Operations')->first();

        // Create parent menus
        $dashboardMenu = Menu::create([
            'name' => 'Dashboard',
            'icon' => 'dashboard',
            'route' => 'dashboard',
            'path' => '/dashboard',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $quotesMenu = Menu::create([
            'name' => 'Quotes',
            'icon' => 'file-text',
            'route' => 'quotes',
            'path' => '/#',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $usersMenu = Menu::create([
            'name' => 'Users',
            'icon' => 'users',
            'route' => 'users',
            'path' => '/users',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $settingsMenu = Menu::create([
            'name' => 'Settings',
            'icon' => 'settings',
            'route' => 'settings',
            'path' => '/#',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
            'is_visible' => true,
        ]);

        // Create child menus for Quotes
        $createQuoteMenu = Menu::create([
            'name' => 'Create Quote',
            'icon' => 'plus-circle',
            'route' => 'quotes.create',
            'path' => '/quotes/create',
            'parent_id' => $quotesMenu->id,
            'order' => 1,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $quoteListMenu = Menu::create([
            'name' => 'Quote List',
            'icon' => 'list',
            'route' => 'quotes.index',
            'path' => '/quotes',
            'parent_id' => $quotesMenu->id,
            'order' => 2,
            'is_active' => true,
            'is_visible' => true,
        ]);

        // Create child menus for Settings
        $menuManagementMenu = Menu::create([
            'name' => 'Menu Management',
            'icon' => 'menu',
            'route' => 'menus',
            'path' => '/settings/menus',
            'parent_id' => $settingsMenu->id,
            'order' => 1,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $userTypesMenu = Menu::create([
            'name' => 'User Types',
            'icon' => 'user-check',
            'route' => 'user-types',
            'path' => '/settings/user-types',
            'parent_id' => $settingsMenu->id,
            'order' => 2,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $paymentLinksMenu = Menu::create([
            'name' => 'Payment Links',
            'icon' => 'link',
            'route' => 'payment-links',
            'path' => '/settings/payment-links',
            'parent_id' => $quotesMenu->id,
            'order' => 3,
            'is_active' => true,
            'is_visible' => true,
        ]);

        // Attach menus to user types
        // Admin has access to all menus
        if ($adminType) {
            $dashboardMenu->userTypes()->attach($adminType->id);
            $quotesMenu->userTypes()->attach($adminType->id);
            $createQuoteMenu->userTypes()->attach($adminType->id);
            $quoteListMenu->userTypes()->attach($adminType->id);
            $usersMenu->userTypes()->attach($adminType->id);
            $settingsMenu->userTypes()->attach($adminType->id);
            $menuManagementMenu->userTypes()->attach($adminType->id);
            $userTypesMenu->userTypes()->attach($adminType->id);
            $paymentLinksMenu->userTypes()->attach($adminType->id);
        }

        // Manager has access to Dashboard, Quotes, and Users (view only)
        if ($managerType) {
            $dashboardMenu->userTypes()->attach($managerType->id);
            $quotesMenu->userTypes()->attach($managerType->id);
            $createQuoteMenu->userTypes()->attach($managerType->id);
            $quoteListMenu->userTypes()->attach($managerType->id);
            $usersMenu->userTypes()->attach($managerType->id);
        }

        // Operations has access to Dashboard and Quotes only
        if ($operationsType) {
            $dashboardMenu->userTypes()->attach($operationsType->id);
            $quotesMenu->userTypes()->attach($operationsType->id);
            $createQuoteMenu->userTypes()->attach($operationsType->id);
            $quoteListMenu->userTypes()->attach($operationsType->id);
        }
    }
}
