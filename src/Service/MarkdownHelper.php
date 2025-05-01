<?php
namespace App\Service;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
// ← importer la vraie classe EmojiExtension
use Giberti\EmojiExtension\EmojiExtension;

class MarkdownHelper
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $environment = new Environment([]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new EmojiExtension()); // ← celle de Giberti

        $this->converter = new CommonMarkConverter([], $environment);
    }

    public function parse(string $markdown): string
    {
        return $this->converter->convertToHtml($markdown);
    }
}
