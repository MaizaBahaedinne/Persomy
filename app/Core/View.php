<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = self::capture($template, $data);
        if ($layout === null) {
            return $content;
        }
        return self::capture($layout, $data + ['content' => $content]);
    }

    public static function capture(string $__template, array $__data = []): string
    {
        $__file = BASE_PATH . '/app/Views/' . $__template . '.php';
        if (!is_file($__file)) {
            throw new \RuntimeException('View not found: ' . $__template);
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            include $__file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
