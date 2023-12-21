<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

/**
 * Twig extension preventing from duplicating included markup data between calls to the same template / associated markup key
 */
class UniqueMarkupIncluderExtension extends AbstractExtension
{
    /**
     * @var array<string, string>
     */
    private array $includedMarkup;

    public function __construct()
    {
        $this->includedMarkup = [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('include_unique_markup', [$this, 'includeUniqueMarkup']),
        ];
    }

    /**
     * Uniquely include provided markup in the template
     *
     * @param string $key Markup data key identifier
     * @param string $markup Markup data to include
     *
     * @return Markup Provided markup data if not already registered in the included markups, empty markup otherwise
     */
    public function includeUniqueMarkup(string $key, string $markup): Markup
    {
        if (!isset($this->includedMarkup[$key]) || $this->includedMarkup[$key] !== $markup) {
            $this->includedMarkup[$key] = $markup;

            return new Markup($markup, 'UTF-8');
        }

        return new Markup('', 'UTF-8');
    }
}
