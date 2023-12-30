<?php

namespace Zemasterkrom\CloudflareTurnstileBundle\Twig;

use Symfony\Component\Form\Exception\LogicException;
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
    private array $includedMarkups;

    public function __construct()
    {
        $this->includedMarkups = [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('strictly_include_unique_markup', [$this, 'strictlyIncludeUniqueMarkup']),
            new TwigFunction('is_markup_already_included', [$this, 'isMarkupAlreadyIncluded']),
        ];
    }

    /**
     * Strictly uniquely include provided markup in the template
     *
     * @param string $key Markup data key identifier
     * @param string $markup Markup data to include
     *
     * @return Markup Provided markup data if key not already associated in the included markups, empty markup otherwise
     *
     * @throws \LogicException If markup has already been registered and the provided content is not the same as the registered one
     */
    public function strictlyIncludeUniqueMarkup(string $key, string $markup): Markup
    {
        if (!isset($this->includedMarkups[$key])) {
            $this->includedMarkups[$key] = $markup;

            return new Markup($markup, 'UTF-8');
        }

        if ($this->includedMarkups[$key] !== $markup) {
            throw new LogicException(sprintf('Unable to include new markup: strict inclusion has been used and different content has already been registered under the key %s', $key));
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
