<?php

namespace App\Support\Themes;

class LivelatchThemeCatalog
{
    public static function all(): array
    {
        return [
            self::theme('livelatch-default', 'Livelatch Default', 'Clean creator profile with bright Livelatch energy.', 'linear-gradient(135deg, #2563eb, #22d3ee)', ['Inter', 'Poppins', 'Roboto', 'Open Sans', 'Montserrat'], [
                'default' => ['primary' => '#2563eb', 'background' => '#ffffff', 'text' => '#111827', 'buttonRadius' => '8px'],
                'dark' => ['primary' => '#7c3aed', 'background' => '#0b0f1a', 'text' => '#f9fafb', 'buttonRadius' => '10px'],
                'aqua' => ['primary' => '#0891b2', 'background' => '#ecfeff', 'text' => '#164e63', 'buttonRadius' => '14px'],
                'sunrise' => ['primary' => '#f97316', 'background' => '#fff7ed', 'text' => '#431407', 'buttonRadius' => '18px'],
                'mint' => ['primary' => '#059669', 'background' => '#ecfdf5', 'text' => '#064e3b', 'buttonRadius' => '12px'],
                'mono' => ['primary' => '#111827', 'background' => '#f9fafb', 'text' => '#111827', 'buttonRadius' => '6px'],
            ]),
            self::theme('australia', 'Australia', 'Sunburnt coastal gradients, reef shimmer, and outback warmth.', 'linear-gradient(135deg, #f97316, #0891b2)', ['Inter', 'Montserrat', 'Lato', 'Source Sans 3', 'Oswald'], [
                'default' => ['primary' => '#f97316', 'background' => '#fff7ed', 'text' => '#3b1d0b', 'buttonRadius' => '18px'],
                'reef' => ['primary' => '#0891b2', 'background' => '#ecfeff', 'text' => '#083344', 'buttonRadius' => '20px'],
                'outback' => ['primary' => '#c2410c', 'background' => '#ffedd5', 'text' => '#431407', 'buttonRadius' => '10px'],
                'eucalyptus' => ['primary' => '#047857', 'background' => '#f0fdf4', 'text' => '#052e16', 'buttonRadius' => '22px'],
                'opal' => ['primary' => '#7c3aed', 'background' => '#f5f3ff', 'text' => '#2e1065', 'buttonRadius' => '24px'],
                'night-sky' => ['primary' => '#38bdf8', 'background' => '#0f172a', 'text' => '#e0f2fe', 'buttonRadius' => '16px'],
            ]),
            self::theme('minecraft', 'Minecraft', 'Voxel blocks, grassy edges, and punchy pixel adventure.', 'linear-gradient(135deg, #16a34a, #854d0e)', ['Press Start 2P', 'Roboto', 'Inter', 'Oswald', 'Source Sans 3'], [
                'default' => ['primary' => '#16a34a', 'background' => '#dcfce7', 'text' => '#14532d', 'buttonRadius' => '2px'],
                'cave' => ['primary' => '#64748b', 'background' => '#111827', 'text' => '#e5e7eb', 'buttonRadius' => '2px'],
                'nether' => ['primary' => '#dc2626', 'background' => '#2a0909', 'text' => '#fee2e2', 'buttonRadius' => '2px'],
                'diamond' => ['primary' => '#06b6d4', 'background' => '#ecfeff', 'text' => '#164e63', 'buttonRadius' => '2px'],
                'redstone' => ['primary' => '#ef4444', 'background' => '#fff1f2', 'text' => '#450a0a', 'buttonRadius' => '2px'],
                'end' => ['primary' => '#a855f7', 'background' => '#18181b', 'text' => '#faf5ff', 'buttonRadius' => '2px'],
            ]),
            self::theme('anime', 'Anime', 'Soft cel-shaded energy with expressive panels and candy highlights.', 'linear-gradient(135deg, #ec4899, #60a5fa)', ['Poppins', 'Nunito', 'Inter', 'Montserrat', 'Playfair Display'], [
                'default' => ['primary' => '#ec4899', 'background' => '#fff1f2', 'text' => '#4a044e', 'buttonRadius' => '24px'],
                'shonen' => ['primary' => '#f97316', 'background' => '#fff7ed', 'text' => '#431407', 'buttonRadius' => '18px'],
                'moonlight' => ['primary' => '#818cf8', 'background' => '#111827', 'text' => '#e0e7ff', 'buttonRadius' => '22px'],
                'sakura' => ['primary' => '#f472b6', 'background' => '#fdf2f8', 'text' => '#831843', 'buttonRadius' => '28px'],
                'mecha' => ['primary' => '#38bdf8', 'background' => '#0f172a', 'text' => '#e0f2fe', 'buttonRadius' => '8px'],
                'manga' => ['primary' => '#111827', 'background' => '#ffffff', 'text' => '#111827', 'buttonRadius' => '4px'],
            ]),
            self::theme('cars', 'Cars', 'Speed lines, gloss paint, garage lighting, and track-day contrast.', 'linear-gradient(135deg, #dc2626, #111827)', ['Oswald', 'Montserrat', 'Inter', 'Roboto', 'Source Sans 3'], [
                'default' => ['primary' => '#dc2626', 'background' => '#111827', 'text' => '#f9fafb', 'buttonRadius' => '12px'],
                'racing-red' => ['primary' => '#ef4444', 'background' => '#fff1f2', 'text' => '#450a0a', 'buttonRadius' => '10px'],
                'midnight' => ['primary' => '#38bdf8', 'background' => '#020617', 'text' => '#e0f2fe', 'buttonRadius' => '14px'],
                'chrome' => ['primary' => '#64748b', 'background' => '#f8fafc', 'text' => '#0f172a', 'buttonRadius' => '8px'],
                'neon-green' => ['primary' => '#84cc16', 'background' => '#111827', 'text' => '#ecfccb', 'buttonRadius' => '16px'],
                'sunset-drive' => ['primary' => '#f97316', 'background' => '#1f2937', 'text' => '#ffedd5', 'buttonRadius' => '18px'],
            ]),
            self::theme('horses', 'Horses', 'Stable textures, earthy tones, and quiet countryside motion.', 'linear-gradient(135deg, #92400e, #16a34a)', ['Merriweather', 'Lato', 'Playfair Display', 'Source Sans 3', 'Inter'], [
                'default' => ['primary' => '#92400e', 'background' => '#fef3c7', 'text' => '#422006', 'buttonRadius' => '18px'],
                'pasture' => ['primary' => '#15803d', 'background' => '#f0fdf4', 'text' => '#052e16', 'buttonRadius' => '24px'],
                'stable' => ['primary' => '#78350f', 'background' => '#fffbeb', 'text' => '#451a03', 'buttonRadius' => '10px'],
                'dressage' => ['primary' => '#334155', 'background' => '#f8fafc', 'text' => '#0f172a', 'buttonRadius' => '8px'],
                'sunset-ride' => ['primary' => '#ea580c', 'background' => '#fff7ed', 'text' => '#431407', 'buttonRadius' => '22px'],
                'night-stable' => ['primary' => '#f59e0b', 'background' => '#1c1917', 'text' => '#fef3c7', 'buttonRadius' => '16px'],
            ]),
            self::theme('bliss', 'Bliss', 'Rolling green hills, clean sky, and calm early-desktop nostalgia.', 'linear-gradient(135deg, #22c55e, #38bdf8)', ['Inter', 'Open Sans', 'Source Sans 3', 'Lato', 'Roboto'], [
                'default' => ['primary' => '#2563eb', 'background' => '#dbeafe', 'text' => '#082f49', 'buttonRadius' => '22px'],
                'green-hill' => ['primary' => '#16a34a', 'background' => '#ecfccb', 'text' => '#14532d', 'buttonRadius' => '24px'],
                'cloud' => ['primary' => '#0284c7', 'background' => '#f0f9ff', 'text' => '#0c4a6e', 'buttonRadius' => '28px'],
                'golden-hour' => ['primary' => '#eab308', 'background' => '#fefce8', 'text' => '#422006', 'buttonRadius' => '26px'],
                'deep-blue' => ['primary' => '#60a5fa', 'background' => '#0f172a', 'text' => '#dbeafe', 'buttonRadius' => '20px'],
                'fresh' => ['primary' => '#14b8a6', 'background' => '#f0fdfa', 'text' => '#134e4a', 'buttonRadius' => '24px'],
            ]),
            self::theme('heavy-metal', 'Heavy Metal Music', 'High contrast, stage fog, sharp edges, and amplifier grit.', 'linear-gradient(135deg, #ef4444, #020617)', ['Oswald', 'Roboto', 'Montserrat', 'Inter', 'Merriweather'], [
                'default' => ['primary' => '#ef4444', 'background' => '#050505', 'text' => '#f5f5f5', 'buttonRadius' => '4px'],
                'blackout' => ['primary' => '#a3a3a3', 'background' => '#000000', 'text' => '#fafafa', 'buttonRadius' => '2px'],
                'blood-red' => ['primary' => '#b91c1c', 'background' => '#1c0707', 'text' => '#fee2e2', 'buttonRadius' => '6px'],
                'stage-fire' => ['primary' => '#f97316', 'background' => '#18181b', 'text' => '#ffedd5', 'buttonRadius' => '8px'],
                'purple-amp' => ['primary' => '#9333ea', 'background' => '#111827', 'text' => '#f3e8ff', 'buttonRadius' => '4px'],
                'silver' => ['primary' => '#71717a', 'background' => '#e5e5e5', 'text' => '#18181b', 'buttonRadius' => '2px'],
            ]),
            self::theme('cyberpunk', 'Cyberpunk', 'Neon signs, scanlines, and late-night city interface glow.', 'linear-gradient(135deg, #f0abfc, #22d3ee)', ['Rajdhani', 'Inter', 'Montserrat', 'Oswald', 'Roboto'], [
                'default' => ['primary' => '#22d3ee', 'background' => '#050816', 'text' => '#e0f2fe', 'buttonRadius' => '10px'],
                'magenta' => ['primary' => '#f0abfc', 'background' => '#190724', 'text' => '#fae8ff', 'buttonRadius' => '12px'],
                'acid' => ['primary' => '#a3e635', 'background' => '#0a0f05', 'text' => '#ecfccb', 'buttonRadius' => '8px'],
                'rain' => ['primary' => '#38bdf8', 'background' => '#020617', 'text' => '#e0f2fe', 'buttonRadius' => '14px'],
                'warning' => ['primary' => '#facc15', 'background' => '#111827', 'text' => '#fef9c3', 'buttonRadius' => '6px'],
                'hotline' => ['primary' => '#fb7185', 'background' => '#160b1f', 'text' => '#ffe4e6', 'buttonRadius' => '18px'],
            ]),
            self::theme('windows-95', 'Windows 95', 'Classic grey chrome, pixel borders, and nostalgic desktop panels.', 'linear-gradient(135deg, #008080, #c0c0c0)', ['Roboto Mono', 'Inter', 'Roboto', 'Source Sans 3', 'Open Sans'], [
                'default' => ['primary' => '#000080', 'background' => '#008080', 'text' => '#000000', 'buttonRadius' => '0px'],
                'desktop' => ['primary' => '#000080', 'background' => '#c0c0c0', 'text' => '#000000', 'buttonRadius' => '0px'],
                'teal' => ['primary' => '#008080', 'background' => '#f5f5f5', 'text' => '#111111', 'buttonRadius' => '0px'],
                'crt' => ['primary' => '#00ff66', 'background' => '#001b12', 'text' => '#d9ffe7', 'buttonRadius' => '0px'],
                'dialog' => ['primary' => '#0000ff', 'background' => '#dfdfdf', 'text' => '#000000', 'buttonRadius' => '0px'],
                'night' => ['primary' => '#c0c0c0', 'background' => '#000080', 'text' => '#ffffff', 'buttonRadius' => '0px'],
            ]),
            self::theme('macos', 'MacOS', 'Frosted glass, soft depth, dock colour, and clean system polish.', 'linear-gradient(135deg, #60a5fa, #f472b6)', ['Inter', 'SF Pro Display', 'Poppins', 'Open Sans', 'Lato'], [
                'default' => ['primary' => '#0a84ff', 'background' => '#f5f5f7', 'text' => '#1d1d1f', 'buttonRadius' => '18px'],
                'graphite' => ['primary' => '#6b7280', 'background' => '#f8fafc', 'text' => '#111827', 'buttonRadius' => '16px'],
                'ventura' => ['primary' => '#ec4899', 'background' => '#fff1f2', 'text' => '#4a044e', 'buttonRadius' => '22px'],
                'dark-glass' => ['primary' => '#60a5fa', 'background' => '#111827', 'text' => '#f8fafc', 'buttonRadius' => '20px'],
                'lime' => ['primary' => '#84cc16', 'background' => '#f7fee7', 'text' => '#365314', 'buttonRadius' => '18px'],
                'studio' => ['primary' => '#a855f7', 'background' => '#faf5ff', 'text' => '#2e1065', 'buttonRadius' => '24px'],
            ]),
        ];
    }

    private static function theme(string $slug, string $name, string $description, string $previewGradient, array $fontFamilies, array $presets): array
    {
        return [
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'status' => 'published',
            'visibility' => 'public',
            'pricing_type' => 'free',
            'version' => '1.0.0',
            's3_asset_prefix' => 'themes/core/' . $slug . '/v1.0.0',
            'manifest' => [
                'description' => $description,
                'previewGradient' => $previewGradient,
                'fontFamilies' => $fontFamilies,
                'presets' => $presets,
                'editableElements' => [
                    'page' => ['background', 'color', 'font-family'],
                    'button' => ['background', 'color', 'border-radius'],
                    'effects' => ['effectIntensity', 'shapeIntensity'],
                ],
                'parameters' => [
                    'effectIntensity' => ['label' => 'Motion', 'min' => 0, 'max' => 100, 'default' => 55],
                    'shapeIntensity' => ['label' => 'Texture', 'min' => 0, 'max' => 100, 'default' => 45],
                ],
            ],
        ];
    }
}
