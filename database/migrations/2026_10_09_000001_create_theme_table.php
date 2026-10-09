<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Detailed Comment: Creates the 'theme' table storing facility-level branding,
     * colors, navbar/header styling, footer styling, buttons, and custom logo paths.
     * Linked to kayakapmd_profile via tenant clientcode identifier.
     */
    public function up(): void
    {
        if (!Schema::hasTable('theme')) {
            Schema::create('theme', function (Blueprint $table) {
                $table->id();
                $table->string('clientcode', 12)->nullable()->index()->comment('Foreign key reference linking to kayakapmd_profile.clientcode');
                $table->string('theme_name', 100)->default('Default Theme')->comment('Descriptive label for theme');
                $table->string('app_background', 50)->default('#f8f9fa')->comment('Overall app body background color');
                $table->string('header_bg', 50)->default('#f4c79f')->comment('Navbar / header background color');
                $table->string('header_text_color', 50)->default('#212529')->comment('Navbar text and icon color');
                $table->string('header_accent_color', 50)->default('#ffa500')->comment('Bottom accent line under navbar');
                $table->string('footer_bg', 50)->default('#f8f9fa')->comment('Application bottom footer background color');
                $table->string('footer_text_color', 50)->default('#6c757d')->comment('Application footer text color');
                $table->string('sidebar_bg', 50)->default('#e9ecef')->comment('Sidebar menu background color');
                $table->string('sidebar_text_color', 50)->default('#212529')->comment('Sidebar navigation text color');
                $table->string('primary_button_bg', 50)->default('#0d6efd')->comment('Primary button background color');
                $table->string('primary_button_text', 50)->default('#ffffff')->comment('Primary button label text color');
                $table->string('secondary_button_bg', 50)->default('#6c757d')->comment('Secondary button background color');
                $table->string('secondary_button_text', 50)->default('#ffffff')->comment('Secondary button label text color');
                $table->string('text_color', 50)->default('#212529')->comment('General application body text color');
                $table->string('logo_path', 255)->nullable()->default('images/logo.png')->comment('Relative asset path to active clinic logo');
                $table->boolean('is_active')->default(true)->comment('Flag indicating if this theme is active for the clinic');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Detailed Comment: Drops the 'theme' table during rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme');
    }
};
