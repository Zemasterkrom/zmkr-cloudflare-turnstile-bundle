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
     * @var array<string, bool>
     */
    private array $includedMarkups;

    public function __construct()
    {
        $this->includedMarkups = [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('include_unique_markup_per_key', [$this, 'includeUniqueMarkupPerKey']),
            new TwigFunction('is_markup_already_included', [$this, 'isMarkupAlreadyIncluded']),
        ];
    }

    /**
     * Uniquely include provided markup in the template by checking against a key tag
     *
     * @param string $key Markup data key identifier
     * @param string $markup Markup data to include
     *
     * @return Markup Provided markup data if key not already associated in the included markups, empty markup otherwise
     */
    public function includeUniqueMarkupPerKey(string $key, string $markup): Markup
    {
        if (!isset($this->includedMarkups[$key])) {
            $this->includedMarkups[$key] = true;

            return new Markup($markup, 'UTF-8');
        }

        return new Markup('', 'UTF-8');
    }

    /**
     * Checks whether a markup identified by a key has already been included in a template
     *
     * @param string $key Markup data key identifier
     *
     * @return bool
     */
    public function isMarkupAlreadyIncluded(string $key): bool
    {
        return isset($this->includedMarkups[$key]);
    }
}
