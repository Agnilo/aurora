<?php

namespace App\Support;

final class RichText
{
    public const PREFIX = 'aurora-rich:v1:';
    public const MAX_LENGTH = 10000;
    public const TEXT_COLORS = [
        ['#28222c', '#1853a3', '#176b83', '#216c4e', '#49691e', '#796000', '#a65413', '#ae343b', '#963e76', '#773c98'],
        ['#85818a', '#3879d6', '#369cbd', '#34a67a', '#81a942', '#c79c16', '#e68b32', '#d95862', '#c75b99', '#a768ce'],
        ['#ffffff', '#cfe3f5', '#c3e9f3', '#cceada', '#dcebb8', '#f5ebad', '#f7d9c6', '#f5c6cd', '#efcce1', '#e9dff2'],
    ];
    public const HIGHLIGHT_COLORS = [
        '#eae5e3',
        '#cfe3f5',
        '#c3e9f3',
        '#cceada',
        '#dcebb8',
        '#f5ebad',
        '#f7d9c6',
        '#f5c6cd',
        '#efcce1',
        '#e9dff2',
    ];

    private const TAGS = ['p', 'div', 'h3', 'h4', 'strong', 'em', 'u', 's', 'ul', 'ol', 'li', 'br', 'span', 'a'];
    private const MAX_NODES = 4000;
    private const MAX_DEPTH = 30;

    public static function normalize(?string $value): ?string
    {
        if ($value === null || !str_starts_with($value, self::PREFIX)) {
            return $value;
        }

        $nodes = self::decode($value);
        if ($nodes === null) {
            return $value;
        }

        if (!self::hasContent($nodes)) {
            return null;
        }

        $json = json_encode($nodes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        return $json === false ? $value : self::PREFIX . $json;
    }

    public static function render(?string $value): string
    {
        $value ??= '';
        $nodes = self::decode($value);

        return $nodes === null
            ? self::escape($value)
            : self::renderNodes($nodes);
    }

    private static function decode(string $value): ?array
    {
        if (!str_starts_with($value, self::PREFIX) || strlen($value) > self::MAX_LENGTH * 4) {
            return null;
        }

        try {
            $nodes = json_decode(substr($value, strlen(self::PREFIX)), true, 128, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($nodes) || !array_is_list($nodes)) {
            return null;
        }

        $count = 0;
        return self::cleanNodes($nodes, 0, $count);
    }

    private static function cleanNodes(array $nodes, int $depth, int &$count): ?array
    {
        if ($depth > self::MAX_DEPTH) {
            return null;
        }

        $result = [];
        foreach ($nodes as $node) {
            if (++$count > self::MAX_NODES) {
                return null;
            }

            if (is_string($node)) {
                $result[] = $node;
                continue;
            }

            if (
                !is_array($node) || !array_is_list($node) || count($node) !== 3
                || !is_string($node[0]) || !is_array($node[1])
                || !is_array($node[2]) || !array_is_list($node[2])
            ) {
                return null;
            }

            [$tag, $attrs, $children] = $node;
            $children = self::cleanNodes($children, $depth + 1, $count);
            if ($children === null) {
                return null;
            }

            if (!in_array($tag, self::TAGS, true)) {
                array_push($result, ...$children);
                continue;
            }

            $clean = [];
            if (
                isset($attrs['color']) && is_string($attrs['color'])
                && in_array($attrs['color'], array_merge(...self::TEXT_COLORS), true)
            ) {
                $clean['color'] = $attrs['color'];
            }
            if (
                isset($attrs['background']) && is_string($attrs['background'])
                && in_array($attrs['background'], self::HIGHLIGHT_COLORS, true)
            ) {
                $clean['background'] = $attrs['background'];
            }
            if ($tag === 'a') {
                $href = $attrs['href'] ?? null;
                if (!is_string($href) || !self::safeUrl($href)) {
                    $tag = 'span';
                } else {
                    $clean['href'] = $href;
                }
            }
            if ($tag === 'br') {
                $clean = [];
                $children = [];
            }

            $result[] = [$tag, (object) $clean, $children];
        }

        return $result;
    }

    private static function safeUrl(string $url): bool
    {
        return strlen($url) <= 2048
            && preg_match('~\Ahttps?://~i', $url) === 1
            && !preg_match('~[\x00-\x20\x7f\\\\]~', $url)
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private static function hasContent(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if (is_string($node)) {
                if (preg_match('/[^\s\x{00a0}\x{200b}]/u', $node) === 1) {
                    return true;
                }
            } elseif (self::hasContent($node[2])) {
                return true;
            }
        }

        return false;
    }

    private static function renderNodes(array $nodes): string
    {
        $html = '';
        foreach ($nodes as $node) {
            if (is_string($node)) {
                $html .= self::escape($node);
                continue;
            }

            [$tag, $attrs, $children] = $node;
            $attrs = (array) $attrs;
            if ($tag === 'br') {
                $html .= '<br>';
                continue;
            }

            $style = '';
            if (isset($attrs['color'])) {
                $style .= 'color:' . $attrs['color'] . ';';
            }
            if (isset($attrs['background'])) {
                $style .= 'background-color:' . $attrs['background'] . ';';
            }
            $html .= '<' . $tag;
            if ($style !== '') {
                $html .= ' style="' . self::escape($style) . '"';
            }
            if ($tag === 'a') {
                $html .= ' href="' . self::escape($attrs['href']) . '" target="_blank" rel="noopener noreferrer nofollow"';
            }
            $html .= '>' . self::renderNodes($children) . '</' . $tag . '>';
        }

        return $html;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
