<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detailed Comment: ThemeModel represents the 'theme' database table storing
 * clinic facility visual styling, brand colors, header, footer, button styles,
 * and custom logo configurations. It is linked to KayakapProfileModel via clientcode.
 */
class ThemeModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'theme';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'clientcode',
        'theme_name',
        'app_background',
        'header_bg',
        'header_text_color',
        'header_accent_color',
        'footer_bg',
        'footer_text_color',
        'sidebar_bg',
        'sidebar_text_color',
        'primary_button_bg',
        'primary_button_text',
        'secondary_button_bg',
        'secondary_button_text',
        'text_color',
        'logo_path',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Detailed Comment: Belongs-to relationship associating the theme with the
     * clinic/facility profile in kayakapmd_profile using the shared clientcode business key.
     */
    public function profile()
    {
        return $this->belongsTo(KayakapProfileModel::class, 'clientcode', 'clientcode');
    }

    /**
     * Detailed Comment: Retrieves the active theme for the facility or creates/returns
     * a sensible default instance if none exists in the database.
     *
     * @param string|null $clientcode
     * @return self
     */
    public static function getActiveTheme(?string $clientcode = null): self
    {
        $query = static::where('is_active', true);

        if ($clientcode) {
            $query->where('clientcode', $clientcode);
        }

        $theme = $query->first();

        if (!$theme) {
            $theme = static::first();
        }

        if (!$theme) {
            // Detailed Comment: Fallback default instance with original clinic brand colors
            $theme = new static([
                'theme_name'           => 'Default Theme',
                'app_background'       => '#f8f9fa',
                'header_bg'            => '#f4c79f',
                'header_text_color'    => '#212529',
                'header_accent_color'  => '#ffa500',
                'footer_bg'            => '#f8f9fa',
                'footer_text_color'    => '#6c757d',
                'sidebar_bg'           => '#e9ecef',
                'sidebar_text_color'   => '#212529',
                'primary_button_bg'    => '#0d6efd',
                'primary_button_text'  => '#ffffff',
                'secondary_button_bg'  => '#6c757d',
                'secondary_button_text'=> '#ffffff',
                'text_color'           => '#212529',
                'logo_path'            => 'images/logo.png',
                'is_active'            => true,
            ]);
        }

        return $theme;
    }
}
