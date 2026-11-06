<?php

declare(strict_types=1);

namespace App\Actions\Events;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class SanitizeEventContent
{
    /**
     * Keep regular markup and strip what could run code on the public event page.
     * Class, style and id stay, because administrators build their layouts with them.
     */
    public function handle(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowAttribute('class', '*')
            ->allowAttribute('style', '*')
            ->allowAttribute('id', '*')
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            ->withMaxInputLength(500_000);

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
