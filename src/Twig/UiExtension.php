<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Reproduit les icônes SVG inline (style trait, 24x24, currentColor) du
 * prototype sans dépendre d'une bibliothèque d'icônes externe — juste les
 * tracés réellement utilisés, extraits tels quels.
 */
final class UiExtension extends AbstractExtension
{
    private const array ICONS = [
        'grid' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'table' => '<path d="M3 3h18v18H3z"/><path d="M3 9h18M9 3v18"/>',
        'trending-up' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
        'message-circle' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>',
        'users' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
        'dollar-sign' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>',
        'file' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'file-check' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>',
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'message-square' => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
        'bell' => '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>',
        'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M4.93 19.07l1.41-1.41M19.07 19.07l-1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>',
    ];

    private const array TAG_CLASSES = [
        'active' => 'tag-green',
        'confirmed' => 'tag-green',
        'pending' => 'tag-gold',
        'suspended' => 'tag-orange',
        'rejected' => 'tag-red',
        'closed' => 'tag-red',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', $this->renderIcon(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('initials', $this->initials(...)),
            new TwigFilter('tag_class', fn(string $status): string => self::TAG_CLASSES[$status] ?? 'tag-blue'),
        ];
    }

    private function renderIcon(string $name, string $class = ''): string
    {
        $inner = self::ICONS[$name] ?? '';

        return sprintf(
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="%s">%s</svg>',
            htmlspecialchars($class, ENT_QUOTES),
            $inner,
        );
    }

    private function initials(string $fullName): string
    {
        $parts = array_filter(preg_split('/\s+/', trim($fullName)));
        $letters = array_map(static fn(string $p) => mb_strtoupper(mb_substr($p, 0, 1)), $parts);

        return implode('', \array_slice($letters, 0, 2)) ?: '--';
    }
}
