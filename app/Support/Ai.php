<?php

namespace App\Support;

/**
 * AI disclosure helpers — normalisation + badge resolution for theme manifests.
 *
 * The manifest `ai` block (see docs/policies/ai-use-policy.md) looks like:
 *   "ai": { "category": "assisted", "tools": ["Claude"], "scope": ["code"],
 *           "notes": "...", "badgeColor": "#D97757" }
 *
 * @see config/ai.php for the approved tools + colours.
 */
class Ai
{
    /**
     * Clean an incoming `ai` block to policy-legal values. Drops anything the
     * policy forbids (e.g. art/video/audio scope), clamps notes, validates the
     * badge colour. Always returns a well-formed array.
     */
    public static function normalize($ai): array
    {
        $ai = is_array($ai) ? $ai : [];

        $categories = config('ai.categories', ['none', 'assisted', 'generated']);
        $category = in_array($ai['category'] ?? 'none', $categories, true) ? $ai['category'] : 'none';

        $tools = array_values(array_filter(
            array_map(fn ($t) => trim((string) $t), (array) ($ai['tools'] ?? [])),
            fn ($t) => $t !== ''
        ));
        $tools = array_slice($tools, 0, 8);

        $allowedScope = config('ai.scopes', ['code', 'text']);
        $scope = array_values(array_intersect(
            array_map(fn ($s) => strtolower(trim((string) $s)), (array) ($ai['scope'] ?? [])),
            $allowedScope
        ));

        $out = ['category' => $category, 'tools' => $tools, 'scope' => $scope];

        if (!empty($ai['notes'])) {
            $out['notes'] = mb_substr((string) $ai['notes'], 0, 500);
        }
        if (!empty($ai['badgeColor']) && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $ai['badgeColor'])) {
            $out['badgeColor'] = strtoupper((string) $ai['badgeColor']);
        }

        return $out;
    }

    /**
     * Resolve the consumer-facing badge for a manifest, or null when the theme
     * declares no AI use (category none / missing). Shape:
     *   ['category','label','color','slug','iconUrl','tools']
     */
    public static function badge(array $manifest): ?array
    {
        $ai = $manifest['ai'] ?? [];
        $category = $ai['category'] ?? 'none';
        if (!in_array($category, ['assisted', 'generated'], true)) {
            return null;
        }

        $tools = array_values(array_filter(array_map('strval', (array) ($ai['tools'] ?? []))));
        $toolsCfg = config('ai.tools', []);

        $slug = null;
        $color = null;
        foreach ($tools as $tool) {
            if (isset($toolsCfg[$tool])) {
                $slug = $toolsCfg[$tool]['slug'];
                $color = $toolsCfg[$tool]['color'];
                break;
            }
        }

        $override = $ai['badgeColor'] ?? null;
        if (is_string($override) && preg_match('/^#[0-9a-fA-F]{6}$/', $override)) {
            $color = strtoupper($override);
        }
        $color = $color ?: config('ai.default_color', '#D97757');

        return [
            'category' => $category,
            'label'    => $category === 'generated' ? 'AI Generated' : 'AI Assisted',
            'color'    => $color,
            'slug'     => $slug,
            'iconUrl'  => $slug ? ('https://cdn.simpleicons.org/' . $slug) : null,
            'tools'    => $tools,
        ];
    }
}
