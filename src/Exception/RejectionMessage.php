<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Exception;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The user-facing message of one rejected query parameter, translated on demand.
 *
 * It resolves `query_filter.<reason>` in the `query_filter` domain, passing the offending value as `%value%`.
 */
final readonly class RejectionMessage implements TranslatableInterface
{
    public function __construct(
        public RejectionReason $reason,
        public string|null $value = null,
    ) {
    }

    public function trans(TranslatorInterface $translator, string|null $locale = null): string
    {
        return $translator->trans('query_filter.' . $this->reason->value, ['%value%' => $this->value ?? ''], 'query_filter', $locale);
    }
}
